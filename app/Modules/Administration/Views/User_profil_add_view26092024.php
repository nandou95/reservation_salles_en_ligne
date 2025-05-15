<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>
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
                <div class="card-body">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black"><?=lang('messages_lang.add_profile')?></h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?=base_url('Administration/User_profil')?>" style="float: right;margin-right: 80px;margin-top:15px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?=lang('messages_lang.link_list')?></a>
                    </div>
                  </div>
                  <div class="table-responsive container " style="margin-top:50px">
                    <form name="myform" id="myform" action="<?=base_url('Administration/User_profil/insert')?>" method="POST" enctype="multipart/form-data">
                      <div class="card-body">
                        
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for=""><?=lang('messages_lang.label_descr_profil')?><span style="color: red;">*</span></label>
                              <input type="text" class="form-control" id="PROFIL_DESCR" name="PROFIL_DESCR" value="<?=set_value('PROFIL_DESCR')?>" autofocus>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('PROFIL_DESCR'); ?>
                              <?php endif ?>
                              <span class="text-danger" id="error_PROFIL_DESCR"></span>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label><?=lang('messages_lang.label_niv_intervention')?><span style="color: red;">*</span></label>
                              <select onchange="getVisualisation()" class="form-control" name="PROFIL_NIVEAU_ID" id="PROFIL_NIVEAU_ID">
                                <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                                <?php
                                foreach($profil_niveau as $value)
                                {
                                  if ($value->PROFIL_NIVEAU_ID==set_value('PROFIL_NIVEAU_ID'))
                                  { 
                                    echo "<option value='".$value->PROFIL_NIVEAU_ID."' selected>".$value->DESC_PROFIL_NIVEAU."</option>";
                                  }
                                  else
                                  {
                                    ?>
                                    <option value="<?=$value->PROFIL_NIVEAU_ID ?>"><?=$value->DESC_PROFIL_NIVEAU?></option>
                                    <?php
                                  }
                                }
                                ?>
                              </select>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('PROFIL_NIVEAU_ID'); ?>
                              <?php endif ?>
                              <span id="error_PROFIL_NIVEAU_ID" class="text-danger"></span>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label><?=lang('messages_lang.label_niv_visual')?><span style="color: red;">*</span></label>
                              <select class="form-control" name="NIVEAU_VISUALISATION_ID" id="NIVEAU_VISUALISATION_ID">
                                <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                                
                              </select>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('NIVEAU_VISUALISATION_ID'); ?>
                              <?php endif ?>
                              <span id="error_NIVEAU_VISUALISATION_ID" class="text-danger"></span>
                            </div>
                          </div>
                        </div>                        
                        
                        <div class="row">
                          <div class="col-md-12">
                            <h1><?=lang('messages_lang.label_droits')?></h1>
                            <span class="text-danger" id="error"></span>
                          </div>
                        </div>
                        <hr>
                        <br>
                        
                        <!--debut droit administration-->
                        <div class="row" id="div_administration">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_admin')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="UTILISATEURS"><span style="margin-left:5px;"></span> <?=lang('messages_lang.labelle_UTILISATEUR')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PROFIL"><span style="margin-left:5px;"></span> <?=lang('messages_lang.labelle_et_mod_prof')?>
                          </div>
                        </div>
                        <hr>
                        <br>
                        <!--fin droit administration-->
                        <!--debut droit tableau de bord -->
                        <div class="row" id="div_administration">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_dash')?></h4>
                          </div>
                          <br> 

                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_TAUX_TCD_ENGAGEMENT"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_taux_TCD_engagement')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_TAUX_EXECUTION_PHASE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_taux_execution_phase')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_TCD_VALEUR_PHASE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_TCD_valeur_phase')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_TCD_VALEUR_INSTITUTION"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_TCD_valeur_institution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_TCD_budget_vote_institution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PERFORMANCE_EXECUTION"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_performance_execution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_BUDGET"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_budget')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_EXECUTION_BUDGETAIRE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_execution_budgetaire')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_vote_execution_budgetaire')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_GRANDE_MASSE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_grande_masse')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_allocation_budget_institution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.menu_depassement_budget_vote')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_TRANSFERT"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_tb_trans')?>
                          </div>
                        </div>
                        <hr>
                        <br>
                        <!-- fin droit tableau de bord -->
                        <!--debut droit tableau de bord pip-->
                        <div class="row" id="div_tableau_bord_pip">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_dash')?> PIP</h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_TDB_PIP"> <span style="margin-left:5px;"></span> TDB PIP
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_MINISTRE_INSTITUTION"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_min_inst')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_FINANCEMENT"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_source_financement')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_prog_budg')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_STATUT_PROJET"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_statu_proj')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_PILIER"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_pilier')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_obj_strateg')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TABLEAU_BORD_PIP_AXE_INTERVENTION"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.label_pip_axe_interv')?>
                          </div>
                        </div>
                        <hr>
                        <br>
                        <!--fin droit tableau de bord pip-->
                        <div class="row" id="div_rapport">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_droit_rapport')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="RAPPORTS_SUIVI_EVALUATION"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_suivi_ev')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="RAPPORTS_CLASSIFICATION_ECONOMIQUE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_class_eco')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="RAPPORTS_CLASSIFICATION_ADMINISTRATIVE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_class_admin')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="RAPPORTS_CLASSIFICATION_FONCTIONNEL"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_class_fonc')?>
                          </div>
                        </div>
                        <hr>
                        <br>

                        <div class="row" id="div_ptba">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_droit_ptba')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_INSTITUTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.labelle_institution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_PROGRAMMES"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_program')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_ACTIONS"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_action')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_ACTIVITES"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_activite')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_CLASSIFICATION_ECONOMIQUE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_class_eco')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_CLASSIFICATION_ADMINISTRATIVE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_class_admin')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PTBA_CLASSIFICATION_FONCTIONNELLE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_class_fonc')?>
                          </div>
                        </div>
                        <hr>
                        <br>                        

                        <div class="row" id="div_double_commande">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_doubcom')?></h4>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.labelle_eng_budget')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_BUDGETAIRE"><span style="margin-left:5px;"></span><?=lang('messages_lang.labelle_eng_budget')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_BUDGETAIRE_SANS_BON"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_budg_sans_bon')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_CONFIRMATION_ENGAGEMENT_BUDGETAIRE_CED"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_budg_val')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_BUDGETAIRE_CORRECTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_budg_corrij')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_BUDGETAIRE_ANNULER"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_eng_budg_annuler')?>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.labelle_eng_jud')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_JURIDIQUE"><span style="margin-left:5px;"></span><?=lang('messages_lang.engagement_juridique')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_CONFIRMATION_ENGAGEMENT_JURIDIQUE"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_jur_valider')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_JURIDIQUE_CORRECTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_jur_corriger')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_JURIDIQUE_ANNULER"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_eng_jur_annuler')?>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.labelle_liquidation')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ENGAGEMENT_LIQUIDATION"><span style="margin-left:5px;"></span><?=lang('messages_lang.labelle_liquidation')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_CONFIRMATION_ENGAGEMENT_LIQUIDATION"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_liquid_val')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_LIQUIDATION_CORRECTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.menu_liquid_corr')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_LIQUIDATION_ANNULER"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_liquid_annuler')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_LIQUIDATION_DECISION_CED"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_liquid_decision_ced')?>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.labelle_ordonan')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ORDONNANCEMENT"><span style="margin-left:5px;"></span><?=lang('messages_lang.labelle_ordonan')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ORDONNANCEMENT_MINISTRE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_ordo_par_min')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ORDONNANCEMENT_DEJA_VALIDE"><span style="margin-left:5px;"></span><?=lang('messages_lang.ordo_deja_valid')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TRANSMISSION_SERVICE_PRISE_COMPTE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_trans_budget_spe')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TRANSMISSION_BON_CABINET"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_transmis_cabinet')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TRANSMISSION_CABINET_SPE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_trans_cabinet_spe')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="ORDONNANCEMENT_CORRECTION_CED"><span style="margin-left:5px;"></span><?=lang('messages_lang.trans_bon_eng_ced')?>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.labelle_paiement')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_RECEPTION_SERVICE_PRISE_COMPTE"><span style="margin-left:5px;"></span><?=lang('messages_lang.rec_spe')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TRANSMISSION_OBR"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_trans_obr')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="RECEPTION_OBR"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_recep_obr')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_AVANT_PRISE_CHARGE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_av_spe')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_PRISE_EN_CHARGE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_prise')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_ETABLISSEMENT_TITRE_DECAISSEMENT"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_etab_dec')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TRANSMISSION_DIRECTEUR_COMPTABLE"><span style="margin-left:5px;"></span><?=lang('messages_lang.trans_dc')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_RECEPTION_DIRECTEUR_COMPTABLE"><span style="margin-left:5px;"></span><?=lang('messages_lang.rec_dc')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TITRE_SIGNATURE_DIR_COMPTABILITE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_titre_dir_compt')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TITRE_SIGNATURE_DGFP"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_titre_dgfp')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TITRE_SIGNATURE_MINISTRE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_titre_min')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DOUBLE_COMMANDE_VALIDE_TD"><span style="margin-left:5px;"></span><?=lang('messages_lang.TitresFait')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_TRANSMISSION_BRB"><span style="margin-left:5px;"></span><?=lang('messages_lang.trans_brb')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_PAIEMENT"><span style="margin-left:5px;"></span><?=lang('messages_lang.labelle_paiement')?>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.labelle_decaisse')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_RECEPTION_BRB"><span style="margin-left:5px;"></span><?=lang('messages_lang.rec_brb')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_DECAISSEMENT"><span style="margin-left:5px;"></span><?=lang('messages_lang.decaissement_decaissement')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="IS_FIN_PROCESSUS"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_fin_proc')?>
                          </div>
                          <br>
                          <br>
                          <div class="col-md-12">
                            <h5 class="text-uppercase"><?=lang('messages_lang.selection_autre')?></h5>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DOUBLE_COMMANDE_ETAT_AVANCEMENT"> <span style="margin-left:5px;"></span> <?=lang('messages_lang.soumen_etat_avanc')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DOUBLE_COMMANDE_TRANSFERT"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_raccr_transfer')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DOUBLE_COMMANDE_PRESTATAIRE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_prestataire')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="SUIVI_EXECUTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.suivi_execution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="SUIVI_PTBA"><span style="margin-left:5px;"></span><?=lang('messages_lang.suivi_ptba')?>
                          </div>
                          
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="TAUX_DOUBLE_COMMANDE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_taux')?>
                          </div>
                        </div>
                        <hr>
                        <br>

                        <div class="row" id="div_pip">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_droit_pip')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PIP_EXECUTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_execution')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PIP_COMPILE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_Compile')?>
                          </div>

                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PIP_TAUX_ECHANGE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_taux')?>
                          </div>

                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PIP_POURCENTAGE_NOMENCLATURE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_pourcentage_nom')?>
                          </div>

                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PIP_SOURCE_FINANCEMENT"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_source_fin')?>
                          </div>
                        </div>
                        <hr>
                        <br>

                        <div class="row" id="div_plan_progr">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_demandes')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DEMANDE_PLANIFICATION_STRATEGIQUE"><span style="margin-left:5px;"></span> <?=lang('messages_lang.label_droit_plan_strat_sect')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DEMANDE_PLANIFICATION_CDMT_CBMT"><span style="margin-left:5px;"></span> <?=lang('messages_lang.label_droit_plan_cdmt_cbmt')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DEMANDE_PROGRAMMATION_BUDGETAIRE"><span style="margin-left:5px;"></span> <?=lang('messages_lang.label_droit_prog_budget')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="DEMANDE_ETAT_AVANCEMENT"><span style="margin-left:5px;"></span> <?=lang('messages_lang.label_etat_av')?>
                          </div>
                        </div>
                        <hr>
                        <br>

                        <div class="row" id="div_configuration_processus">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_parametre')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PARAMETRE_PROCESSUS"><span style="margin-left:5px;"></span><?=lang('messages_lang.labelle_process')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PARAMETRE_ETAPE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_etape')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PARAMETRE_ACTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.actions_action')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PARAMETRE_DOCUMENTS"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droits_doc')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="PARAMETRE_INFO_SUPPLEMENTAIRE"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droits_info_sup')?>
                          </div>
                        </div>
                        <hr>
                        <br>

                        <div class="row" id="div_masque_donnes">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_titre_ihm')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="MASQUE_SAISI_ENJEUX"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_enjeux')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="MASQUE_SAISI_INSTITUTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_institutions')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="MASQUE_SAISI_PTBA_PROGRAMMES"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_ptba_prog')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="MASQUE_SAISI_PTBA_ACTIONS"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_ptba_act')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="MASQUE_SAISI_PTBA_ACTIVITES"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_ptba_activite')?>
                          </div>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="MASQUE_SAISI_OBSERVATION_FINANCIERES"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_observ_fin')?>
                          </div>
                        </div>
                        <hr>
                        <br>                                         

                        <div class="row" id="div_localisation">
                          <div class="col-md-12">
                            <h4><?=lang('messages_lang.label_droit_geolocalisation')?></h4>
                          </div>
                          <br>
                          <div class="col-md-4">
                            <input type="checkbox" value="1" name="GEOLOCALISATION_CARTE_INSTITUTION"><span style="margin-left:5px;"></span><?=lang('messages_lang.label_droit_carte_instit')?>
                          </div>
                        </div>
                        <hr>
                        <br>
                      </div>
                    </form>
                    <div class="form-group" style="float:right;" id="SAVE">
                    <button type="button" class="btn btn-primary float-end envoi" id="btnSave"  onclick="save_educ()"> <i class="fa fa-save" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_enregistrer')?></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
  </div>
  <?php echo view('includesbackend/scripts_js.php'); ?>
</body>
</html>
<script type="text/javascript">
  $('#PROFIL_DESCR').on('input paste change',function()
  {
    $(this).val($(this).val().replace(/[^\p{L}\s'-]/gu, ''));
  });
</script>
<script type="text/javascript">
  function getVisualisation()
  {
    var PROFIL_NIVEAU_ID  = $('#PROFIL_NIVEAU_ID').val();
    $.ajax(
    {
      url: '<?= base_url('Administration/User_profil/getVisualisation')?>/'+PROFIL_NIVEAU_ID,
      type: "POST",
      dataType: "JSON",
      success: function(data)
      {
        $('#NIVEAU_VISUALISATION_ID').html(data.NIVEAU_VISUALISATION_ID)
      }
    });
  }
</script>
<script type="text/javascript">
  function save_educ()
  {
    var PROFIL_DESCR=$('#PROFIL_DESCR').val();
    var PROFIL_NIVEAU_ID=$('#PROFIL_NIVEAU_ID').val();
    var NIVEAU_VISUALISATION_ID  = $('#NIVEAU_VISUALISATION_ID').val();
    $('#error_PROFIL_DESCR').html('');
    $('#error_PROFIL_NIVEAU_ID').html('');
    $('#error_NIVEAU_VISUALISATION_ID').html('');
    $('#error').html('');

    var statut = 2;
    if(PROFIL_DESCR  == '')
    {
      $('#error_PROFIL_DESCR').html('Le champ est obligatoire');
      statut = 1;
    }

    if(PROFIL_NIVEAU_ID  == '')
    {
      $('#error_PROFIL_NIVEAU_ID').html('Le champ est obligatoire');
      statut = 1;
    }

    if(NIVEAU_VISUALISATION_ID  == '')
    {
      $('#error_NIVEAU_VISUALISATION_ID').html('Le champ est obligatoire');
      statut = 1;
    }
    
    if(PROFIL_DESCR.length >100)
    {
      $('#error_PROFIL_DESCR').html('Vous ne pouvez pas saisir plus de 100 caractères');
      statut = 1;
    }

    //Obliger l'utilisateur de cocher au moins une case
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    var isChecked = false;
    for (var i = 0; i < checkboxes.length; i++)
    {
      if (checkboxes[i].checked)
      {
        isChecked = true;
        break;
      }
    }

    if (!isChecked)
    {
      $('#error').html('Veillez choisir au moins une case');
      statut=1;
    }

    if(statut == 2)
    {
      $('#myform').submit();
    }
  }
</script>