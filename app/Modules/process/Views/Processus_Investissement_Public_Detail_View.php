<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>

</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">

          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">

                <!-- <br> -->
                <div class="card-body">
                  <!-- <form id="my_form" action="<?= base_url() ?>" method="POST"> -->
                  <div class="card-body">
                    <!-- Titre Etape actuelle -->
                    <div class="row" style="margin :  5px">
                      <div class="col-12">
                        <!-- <h1 class="header-title text-black"><?= !empty($principal[0]->DESCR_ETAPE) ? $principal[0]->DESCR_ETAPE : "N/A" ?></h1> -->
                        <h5><i class="fa fa-circle"></i>&nbsp; <?= $details['NUMERO_PROJET'] . ' ,&nbsp;&nbsp; <i class="fa fa-university"></i>&nbsp; ' . $details['NOM_PROJET'] ?></h5>
                      </div>
                    </div>
                    <!-- Bouton des Actions et liste -->
                    <div style="border:0px solid #ddd;border-radius:5px;margin: 5px">
                      <div class="row" style="margin :  5px">
                        <div class="col-3">
                          <?php
                          if (!empty($etape)) {
                          ?>
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown"><?= lang('messages_lang.labelle_action') ?>
                                <span class="caret"></span></button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <?php
                                if (!empty(session()->get('SESSION_SUIVIE_PTBA_PROFIL_ID'))) {
                                  foreach ($etape as $keyEtape) {
                                ?>
                                    <li class="px-3 dropdown-item">
                                      <?php if ($keyEtape->ETAPE_ID != $first[0]->ETAPE_ID) : ?>
                                        <?php if ($keyEtape->GET_FORM == 1) : ?>
                                          <a href="<?= base_url() . '/' . $keyEtape->LINK_FORM ?>"><?= $keyEtape->DESCR_ACTION ?></a>
                                        <?php else : ?>
                                          <form action="/pip/Processus_Investissement_Public/Proceed" method="POST" id="form_<?= $keyEtape->ACTION_ID ?>" class="position-absolute">
                                            <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?= $resultat['ID_DEMANDE'] ?>">
                                            <input type="hidden" name="CURRENT_STEP" id="CURRENT_STEP" value="<?= $keyEtape->ETAPE_ID ?>">
                                            <input type="hidden" name="ACTION_ID" id="ACTION_ID" value="<?= $keyEtape->ACTION_ID ?>">
                                            <input type="hidden" name="IS_CORRECTION" id="IS_CORRECTION" value="<?= $keyEtape->IS_CORRECTION_PIP ?>">
                                            <textarea class="d-none" name="FORM_COMMENTAIRE" id="FORM_COMMENTAIRE"></textarea>
                                          </form>
                                          <div style="cursor: pointer;" id="openModal" data-toggle="modal" data-target="#addCommentaire"><?= $keyEtape->DESCR_ACTION ?></div>
                                        <?php endif; ?>
                                      <?php elseif ($keyEtape->ETAPE_ID == $last[0]->ETAPE_ID) : ?>
                                        <div style="cursor: pointer;"><?= lang('messages_lang.action_valider_PIP') ?></div>
                                      <?php else : ?>
                                        <a href="/pip/Processus_Investissement_Public/demande/update/<?= $resultat['ID_DEMANDE'] ?>"><?= lang('messages_lang.action_corriger_fiche') ?></a>
                                      <?php endif; ?>
                                    <?php
                                  }
                                } 
                                  ?>
                              </ul>
                            </div>
                          <?php
                          } else {
                          ?>
                            <a href="#" onclick="history.go(-1)" class="btn btn-primary"><i class="fa fa-reply-all"></i> <?= lang('messages_lang.action_retour') ?> </a>
                          <?php } ?>
                        </div>
                        <div class="col-6"></div>
                        <div class="col-3">
                          <a href="<?= base_url('pip/Projet_Pip_Fini/liste_pip_fini') ?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?= lang('messages_lang.link_list') ?></a>
                        </div>
                      </div>
                    </div>
                    <br>
                    <input type="hidden" name="ID_DEMANDE_INFO_SUPP" id="ID_DEMANDE_INFO_SUPP" value="<?= $details['ID_DEMANDE_INFO_SUPP'] ?>">
                    <!-- Info de base de la demande -->
                    <div style="border:0px solid #ddd;border-radius:5px;margin: 0px">
                      <div class="row" style="margin :  0px">
                        <div class="col-12">
                          <!-- <div class="table-responsive" style="width: 100%;"> -->
                          <table class=" table table-striped table-bordered">
                            <tr>
                              <th>
                                <center><?= lang('messages_lang.labelle_numero_projet') ?></center>
                              </th>
                              <th>
                                <center><?= lang('messages_lang.labelle_inst_min') ?> </center>
                              </th>
                              <th>
                                <center><?= lang('messages_lang.Lab_jur_etape') ?> </center>
                              </th>
                              <th>
                                <center><?= lang('messages_lang.th_date') ?> </center>
                              </th>
                              <th>
                                <center><?= lang('messages_lang.th_initiateur') ?></center>
                              </th>
                            </tr>
                            <tr>
                              <td><?= !empty($details['NUMERO_PROJET']) ? $details['NUMERO_PROJET'] : "N/A" ?></td>
                              <td><?= !empty($details['DESCRIPTION_INSTITUTION']) ? $details['DESCRIPTION_INSTITUTION'] : "N/A" ?></td>
                              <td><?= !empty($resultat['DESCR_ETAPE']) ? $resultat['DESCR_ETAPE'] : "N/A" ?></td>
                              <td><?= !empty($resultat['DATE_INSERTION']) ? date('d-m-Y', strtotime($resultat['DATE_INSERTION'])) : "N/A" ?></td>
                              <td><?= !empty($resultat['NOM'] . ' ' . $resultat['PRENOM']) ? $resultat['NOM'] . ' ' . $resultat['PRENOM'] : "N/A" ?></td>
                            </tr>
                          </table>
                          <!-- </div> -->
                        </div>
                      </div>
                    </div>

                    <!-- information detaillé de la demande -->
                    <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                      <div class="row" style="margin :  5px">
                        <div class="col-12">
                          <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                              <button class="nav-link active" id="pills-tab1-tab" data-toggle="pill" data-target="#pills-tab1" type="button" role="tab" aria-controls="pills-tab1" aria-selected="true"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.bouton_detail') ?></button>
                              <button class="nav-link" id="pills-tab2-tab" data-toggle="pill" data-target="#pills-tab2" type="button" role="tab" aria-controls="pills-tab2" aria-selected="false"><i class="fa fa-history" aria-hidden="true"></i> <?= lang('messages_lang.histo_btn') ?></button>

                              <button class="nav-link" id="pills-tab4-tab" data-toggle="pill" data-target="#pills-tab4" type="button" role="tab" aria-controls="pills-tab4" aria-selected="false"><i class="fa fa-map" aria-hidden="true"></i> <?= lang('messages_lang.tab_lieu_intervention') ?></button>
                              <button class="nav-link" id="pills-tab3-tab" data-toggle="pill" data-target="#pills-tab3" type="button" role="tab" aria-controls="pills-tab3" aria-selected="false"><i class="fa fa-file" aria-hidden="true"></i> <?= lang('messages_lang.tab_contexte_projet') ?></button>

                              <button class="nav-link" id="pills-tab5-tab" data-toggle="pill" data-target="#pills-tab5" type="button" role="tab" aria-controls="pills-tab5" aria-selected="false"><i class="fa fa-credit-card"></i> <?= lang('messages_lang.labelle_sfp') ?> </button>

                              <button class="nav-link" id="pills-tab6-tab" data-toggle="pill" data-target="#pills-tab6" type="button" role="tab" aria-controls="pills-tab6" aria-selected="false"><i class="fa fa-folder" aria-hidden="true"></i> <?= lang('messages_lang.tab_autres') ?></button>
                            </div>
                          </nav>

                          <div class="tab-content" id="nav-tabContent">
                            <!-- tab pour le détail -->
                            <div style="background-color: white" class="tab-pane  show active" id="pills-tab1" aria-labelledby="pills-tab1-tab">
                              <div class="table-responsive" style="width: 100%;">
                                <div class="row">
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><i class="fa fa-hourglass-start"></i> <?= lang('messages_lang.labelle_statut_du_projet') ?></label>
                                    <p><b><?= $details['DESCR_STATUT_PROJET'] ?? '-' ?></b></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><i class="fa fa-calendar-minus"></i> <?= lang('messages_lang.labelle_date_de_debut') ?></label>
                                    <p><?= $date_debut ?? '-' ?></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><i class="fa fa-calendar-plus"></i> <?= lang('messages_lang.labelle_date_de_fin') ?></label>
                                    <p><?= $date_fin ?? '-' ?></p>
                                  </div>
                                  <div class="form-group col-md-3">
                                    <label class="font-weight-bold"><i class="fa fa-calendar-check"></i> <?= lang('messages_lang.labelle_duree') ?></label>
                                    <p><b><?= $details['DUREE_PROJET'] ?? '-' ?></b></p>
                                  </div>
                                </div><br>
                                <div class="row">
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-globe"></span> <?= lang('messages_lang.labelle_perimetre') ?> </label>
                                    <p><b><?= $details['EST_REALISE_NATIONAL'] == '1' ? lang('messages_lang.label_oui') : lang('messages_lang.label_non') ?></b></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-bullseye"></span> <?= lang('messages_lang.labelle_pilier') ?></label>
                                    <p><?= $details['DESCR_PILIER'] ?? '-' ?></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-dot-circle"></span> <?= lang('messages_lang.labelle_objectif_strategique') ?> </label>
                                    <p><?= $details['DESCR_OBJECTIF_STRATEGIC'] ?? '-' ?></p>
                                  </div>
                                  <div class="form-group  col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-circle"></span> <?= lang('messages_lang.labelle_objectif_strategique_PND') ?></label>
                                    <p><b><?= $details['DESCR_OBJECTIF_STRATEGIC_PND'] ?? '-' ?></b></p>
                                  </div>
                                </div><br>
                                <div class="row">
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-map"></span> <?= lang('messages_lang.labelle_axe_intervention_PND') ?> </label>
                                    <p><b><?= $details['DESCR_AXE_INTERVATION_PND'] ?? '-' ?></b></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-level-up"></span> <?= lang('messages_lang.labelle_programme_budget') ?></label>
                                    <p><?= $details['INTITULE_PROGRAMME'] ?? '-' ?></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-pencil"></span> <?= lang('messages_lang.label_action') ?> </label>
                                    <p><?= $details['LIBELLE_ACTION'] ?? '-' ?></p>
                                  </div>
                                  <div class="form-group col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-sliders"></span> <?= lang('messages_lang.labelle_programme_prioritaire') ?></label>
                                    <p><b><?= $details['DESCR_PROGRAMME'] ?? '-' ?></b></p>
                                  </div>
                                </div><br>
                                <div class="row">
                                  <div class="form-group col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-tree"></span> <?= lang('messages_lang.labelle_impact_env_min') ?></label>
                                    <p><b><?= $details['IMPACT_ATTENDU_ENVIRONNEMENT'] ?? '-' ?></b></p>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-id-card"></span> <?= lang('messages_lang.labelle_impact_genre_min') ?> </label>
                                    <p><b><?= $details['IMPACT_ATTENDU_GENRE'] ?? '-' ?></b></p>
                                  </div>

                                  <div class="form-group col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-calendar"></span> <?= lang('messages_lang.labelle_date_preparation') ?></label>
                                    <strong><?= $details['DATE_PREPARATION_FICHE_PROJET'] ?? 'N/A' ?></strong>
                                  </div>
                                  <div class="col-md-3">
                                    <label class="font-weight-bold"><span class="fa fa-file"></span> <?= lang('messages_lang.tab_observation_complementaire') ?> </label>
                                    <p><strong><?= $details['OBSERVATION_COMPLEMENTAIRE'] ?? '-' ?></strong></p>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- tab pour historique -->
                            <div class="tab-pane fade" id="pills-tab2" aria-labelledby="pills-tab2-tab">
                              <div class="table-responsive" style="width: 100%;">
                                <table id="mytable2" class=" table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.labelle_et_etapes') ?></th>
                                      <th><?= lang('messages_lang.label_action') ?></th>
                                      <th><?= lang('messages_lang.labelle_commentaire') ?></th>
                                      <th><?= lang('messages_lang.labelle_UTILISATEUR') ?></th>
                                      <th><?= lang('messages_lang.labelle_date_traitement') ?></th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                    if (count($historics)) :
                                      $i = 1;
                                      foreach ($historics as $hist) :
                                    ?>
                                        <tr>
                                          <td><?= $i ?></td>
                                          <td><?= $hist->DESCR_ETAPE ?></td>
                                          <td><?= $hist->DESCR_ACTION ?></td>
                                          <td><?= $hist->COMMENTAIRE ?? '-' ?></td>
                                          <td><?= $hist->USER_NAME ?></td>
                                          <td><?= date_format(new \DateTime($hist->DATE_INSERTION), "d/m/Y") ?></td>
                                        </tr>
                                      <?php
                                        $i++;
                                      endforeach;
                                    else :
                                      ?>
                                      <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                    <?php
                                    endif;
                                    ?>
                                  </tbody>
                                </table>
                              </div>
                            </div>

                            <!-- tab pour lieu d'intervention -->
                            <div style="background-color: white" class="tab-pane fade" id="pills-tab4" aria-labelledby="pills-tab4-tab">
                              <div class="table-responsive">
                                <table id="mytable_lieu" class="table table-striped table-bordered" style="width: 100%;">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.labelle_provinces') ?></th>
                                      <th><?= lang('messages_lang.labelle_communes') ?></th>
                                    </tr>
                                  </thead>

                                </table>
                              </div>
                            </div>
                            <!-- tab pour les contexte et justification -->
                            <div style="background-color: white" class="tab-pane fade" id="pills-tab3" aria-labelledby="pills-tab3-tab">
                              <br>
                              <div class="row">
                                <div class="col-md-4">
                                  <label class="font-weight-bold"><span class="fa fa-bullseye"></span> <?= lang('messages_lang.labelle_objectif_general') ?></label>
                                  <p><?= $details['OBJECTIF_GENERAL'] ?? 'N/A' ?></p>
                                </div>
                                <div class="col-md-4">
                                  <label class="font-weight-bold"><span class="fa fa-user"></span> <?= lang('messages_lang.labelle_beneficiaire') ?> </label>
                                  <p><?= $details['BENEFICIAIRE_PROJET'] ?? 'N/A' ?></p>
                                </div>
                                <div class="col-md-4">
                                  <label class="font-weight-bold"><span class="fa fa-file"></span> <?= lang('messages_lang.labelle_contexte_justification') ?> </label>
                                  <p><b><?= (mb_strlen($details['PATH_CONTEXTE_JUSTIFICATION']) > 20) ? (mb_substr($details['PATH_CONTEXTE_JUSTIFICATION'], 0, 20) . "...<a class='btn-sm' data-toggle='modal' data-target='#contexte' data-toggle='tooltip' title='" . $details['PATH_CONTEXTE_JUSTIFICATION'] . "'><i class='fa fa-eye'></i></a>") : $details['PATH_CONTEXTE_JUSTIFICATION'] ?></b></p>
                                </div>
                              </div>
                              <hr>
                              <h4><?= lang('messages_lang.labelle_objectif_livrable') ?> </h4>
                              <div class="table-responsive" style="width: 100%;">
                                <table id="mytable5" class=" table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.labelle_objectif_specifique') ?></th>
                                      <th><?= lang('messages_lang.labelle_livrable') ?></th>
                                      <th><?= lang('messages_lang.labelle_cout_unitaire') ?></th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                    if (count($obj_livrables)) :
                                      $i = 1;
                                      foreach ($obj_livrables as $key) :
                                    ?>
                                        <tr>
                                          <td><?= $i ?></td>
                                          <td><?= $key->OBJECTIF_SPECIFIQUE ?? 'N/A' ?></td>
                                          <td><?= $key->DESCR_LIVRABLE ?? 'N/A' ?></td>
                                          <td><?= number_format($key->COUT_LIVRABLE, 2, ",", " "); ?></td>
                                        </tr>
                                      <?php
                                        $i++;
                                      endforeach;
                                    else :
                                      ?>
                                      <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                    <?php
                                    endif;
                                    ?>
                                  </tbody>
                                </table>
                              </div>

                            </div>
                            <!-- tab pour le source de financement -->
                            <div style="background-color: white" class="tab-pane fade" id="pills-tab5" aria-labelledby="pills-tab5-tab">
                              <div class="table-responsive" style="width: 100%;">
                                <div class="row">
                                  <div class="col-md-12">
                                    <label class="font-weight-bold"><i class="fa fa-hourglass-start"></i> <?= lang('messages_lang.labelle_projet_co_finance') ?></label>
                                    <p><b><?= $details['EST_CO_FINANCE'] == '1' ? lang('messages_lang.label_oui') : lang('messages_lang.label_non') ?></b></p>
                                  </div>
                                </div>
                                <table id="mytable_financ" class="table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.labelle_bailleur') ?></th>
                                      <th><?= lang('messages_lang.labelle_devise') ?></th>
                                      <th><?= lang('messages_lang.labelle_total_financement') ?></th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                    if (count($source_financement)) :
                                      $i = 1;
                                      foreach ($source_financement as $key) :
                                    ?>
                                        <tr>
                                          <td><?= $i ?></td>
                                          <td><?= $key->NOM_SOURCE_FINANCE ?? 'N/A' ?></td>
                                          <td><?= $key->DEVISE ?? 'N/A' ?></td>
                                          <td><?= number_format($key->TOTAL_FINANCEMENT, 2, ",", " "); ?></td>
                                        </tr>
                                      <?php
                                        $i++;
                                      endforeach;
                                    else :
                                      ?>
                                      <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                    <?php
                                    endif;
                                    ?>
                                  </tbody>
                                </table>
                              </div>
                            </div>
                            <!-- liste des études faites sur le projet -->
                            <div style="background-color: white" class="tab-pane fade" id="pills-tab6" aria-labelledby="pills-tab6-tab">

                              <?php
                              if (count($etudes)) :
                              ?>
                                <div class="table-responsive" style="width: 100%;">
                                  <h4><?= lang('messages_lang.tab_etude_document') ?></h4>
                                  <table id="table_etude" class="table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.th_titre') ?></th>
                                        <th><?= lang('messages_lang.labelle_statut_etude') ?></th>
                                        <th><?= lang('messages_lang.labelle_annee_etude') ?></th>
                                        <th><?= lang('messages_lang.labelle_document_reference') ?></th>
                                        <th><?= lang('messages_lang.th_statut_juridique') ?></th>
                                        <th><?= lang('messages_lang.th_nom_auteur') ?></th>
                                        <th><?= lang('messages_lang.labelle_nationalite') ?></th>
                                        <th><?= lang('messages_lang.labelle_pays') ?></th>
                                        <th><?= lang('messages_lang.labelle_NIF') ?></th>
                                        <th><?= lang('messages_lang.labelle_registre_commerce') ?></th>
                                        <th><?= lang('messages_lang.labelle_adresse') ?></th>
                                        <th><?= lang('messages_lang.labelle_observartion') ?></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      if (count($etudes)) :
                                        $i = 1;
                                        foreach ($etudes as $key) :
                                      ?>
                                          <tr>
                                            <td><?= $i ?></td>
                                            <td><?= $key->TITRE_ETUDE ?? 'N/A' ?></td>
                                            <td><?= $key->STATUT_ETUDE == '1' ? 'Validé' : 'En cours' ?></td>
                                            <td><?= $key->DATE_REFERENCE ?? 'N/A' ?></td>
                                            <td>
                                              <center><button style="border:none;" type="button" onclick="get_doc_reference('<?= $key->ID_ETUDE_DOC_REF ?>')"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button></center>

                                            </td>
                                            <td><?= $key->STATUT_JURIDIQUE == '1' ? 'select_personne_physique' : 'select_personne_morale' ?></td>
                                            <td><?= $key->AUTEUR_ORGANISME ?? 'N/A' ?></td>
                                            <td><?= $key->NATIONALITE == '1' ? lang('messages_lang.select_etrangere') : lang('messages_lang.select_burundaise') ?></td>
                                            <td><?= $key->CommonName ?? 'N/A' ?></td>
                                            <td><?= $key->NIF_AUTEUR ?? 'N/A' ?></td>
                                            <td><?= $key->REGISTRE_COMMERCIALE ?? 'N/A' ?></td>
                                            <td><?= $key->ADRESSE ?? 'N/A' ?></td>
                                            <td><?= $key->OBSERVATION ?? 'N/A' ?></td>
                                          </tr>


                                        <?php
                                          $i++;
                                        endforeach;
                                      else :
                                        ?>
                                        <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                      <?php
                                      endif;
                                      ?>
                                    </tbody>
                                  </table>

                                </div>
                              <?php endif; ?>
                              <?php
                              if (count($budget_projet)) :
                              ?>
                                <!-- liste du budget du projet -->
                                <div class="table-responsive" style="width: 100%;">
                                  <h4><?= lang('messages_lang.tab_bpl') ?></h4>

                                  <table id="table_budget" class="table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.th_titre') ?></th>
                                        <th><?= lanng('messages_lang.th_statut') ?></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      if (count($budget_projet)) :
                                        $i = 1;
                                        foreach ($budget_projet as $value) :
                                      ?>
                                          <tr>
                                            <td><?= $i ?></td>
                                            <td><?= $value->DESCR_LIVRABLE ?? 'N/A' ?></td>
                                            <td><?= $value->DESCR_NOMENCLATURE ?? 'N/A' ?></td>
                                            <td><?= number_format($value->COUT_UNITAIRE_BIF, 2, ",", " "); ?></td>
                                          </tr>
                                        <?php
                                          $i++;
                                        endforeach;
                                      else :
                                        ?>
                                        <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                      <?php
                                      endif;
                                      ?>
                                    </tbody>
                                  </table>
                                </div>
                              <?php endif;
                              ?>

                              <?php
                              if (count($risques_mitigation)) :
                              ?>
                                <!-- liste des risques du projet-->
                                <div class="table-responsive" style="width: 100%;">
                                  <h4><?= lang('messages_lang.tab_risque_projet') ?></h4>

                                  <table id="table_risques" class="table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.th_risque_associe') ?></th>
                                        <th><?= lang('messages_lang.labelle_mitigation') ?></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      if (count($risques_mitigation)) :
                                        $i = 1;
                                        foreach ($risques_mitigation as $value) :
                                      ?>
                                          <tr>
                                            <td><?= $i ?></td>
                                            <td><?= $value->NOM_RISQUE ?? 'N/A' ?></td>
                                            <td><?= $value->MESURE_RISQUE ?? 'N/A' ?></td>
                                          </tr>
                                        <?php
                                          $i++;
                                        endforeach;
                                      else :
                                        ?>
                                        <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                      <?php
                                      endif;
                                      ?>
                                    </tbody>
                                  </table>
                                </div>
                              <?php endif; ?>
                              <?php
                              if (count($cadre_mesure)) :
                              ?>
                                <!-- liste des cadres de mesure -->
                                <div class="table-responsive" style="width: 100%;">
                                  <h4><?= lang('messages_lang.tab_cmr') ?></h4>

                                  <table id="table_cadre" class="table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.labelle_livrable') ?></th>
                                        <th><?= lang('messages_lang.labelle_indicateur_mesure') ?></th>
                                        <th><?= lang('messages_lang.labelle_unite_mesure') ?></th>
                                        <th><?= lang('messages_lang.th_cible_projet') ?></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      if (count($cadre_mesure)) :
                                        $i = 1;
                                        foreach ($cadre_mesure as $value) :
                                      ?>
                                          <tr>
                                            <td><?= $i ?></td>
                                            <td><?= $value->DESCR_LIVRABLE ?? 'N/A' ?></td>
                                            <td><?= $value->INDICATEUR_MESURE ?? 'N/A' ?></td>
                                            <td><?= $value->UNITE_MESURE ?? 'N/A' ?></td>
                                            <td><?= number_format($value->TOTAL_TRIENNAL, 0, ",", " "); ?></td>
                                          </tr>
                                        <?php
                                          $i++;
                                        endforeach;
                                      else :
                                        ?>
                                        <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                      <?php
                                      endif;
                                      ?>
                                    </tbody>
                                  </table>
                                </div>
                              <?php endif ?>
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                  <!-- </form> -->
                </div>

              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php'); ?>

  <!-- Modal de document de référence -->
  <div class="modal fade" id="doc_reference" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.labelle_document_reference') ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <embed id="document" type="application/pdf" width="100%" height="500px">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal"> <?= lang('messages_lang.label_ferm') ?></button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

<?php if (preg_match("/élaborarion du PIP/i", $resultat['DESCR_ETAPE'])) : ?>
  <div class="modal fade" id="modal_form" tabindex="-1" role="dialog" aria-labelledby="example2ModalLabel" aria-hidden="true" data-backdrop="static">
    <div class='modal-dialog  modal-lg' role="document" style="max-width: 60%">
      <div class='modal-content'>
        <div class="modal-header">
          <h5 class="modal-title"><?= !empty($resultat['DESCR_ETAPE']) ? $resultat['DESCR_ETAPE'] : "N/A" ?></h5>
        </div>
        <div class='modal-body'>
          <div class="row">
            <form action="" method="post">
              <div class="form-group">
                <input type="text" class="form-control">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php else : ?>
  <div class='modal fade' id='addCommentaire' tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class='modal-dialog  modal-lg' role="document" style="max-width: 60%">
      <div class='modal-content'>
        <div class="modal-header">
          <h5 class="modal-title"><?= !empty($resultat['DESCR_ETAPE']) ? $resultat['DESCR_ETAPE'] : "N/A" ?></h5>
        </div>
        <div class='modal-body'>
          <div class="row">
            <input type="hidden" name="" id="form_id">
            <div class="col-12">
              <label><?= lang('messages_lang.labelle_commentaire') ?></label>
              <textarea rows="5" name="COMMENTAIRE" id="COMMENTAIRE" class="form-control"></textarea>
            </div>
          </div>
        </div>
        <div class='modal-footer'>
          <button class='btn btn-danger btn-md' data-dismiss='modal'><i class="fa fa-close"></i> <?= lang('messages_lang.annuler_modal') ?></button>
          <button id="submit_form" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= lang('messages_lang.transmettre_modal') ?></button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <?php if (!empty($reference[0]->DOC_REFERENCE)) { ?>
          <embed id="pdf2" style="display:none;" src="<?= base_url($reference[0]->DOC_REFERENCE) ?>" type="application/pdf" width="100%" height="600px">
        <?php } ?>

      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    lieu_intervention();
    source();
    objectif();
    etudes();
    cadre_mesure();
    budget();
    risques();
    // script pour historique
    $("#mytable2").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });

  });
</script>

<script type="text/javascript">
  // script pour objectif et livrables
  function objectif() {
    $("#mytable5").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
</script>



<script type="text/javascript">
  // script pour etudes
  function etudes() {
    $("#table_etude").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
  ///script pour le budget
  function budget() {
    $("#table_budget").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
</script>
<script type="text/javascript">
  // script pour source de financement
  function source() {
    $("#mytable_financ").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
  ///cadre de mesure
  function cadre_mesure() {
    $("#table_cadre").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
  //risques et mitigation
  function risques() {
    $("#table_risques").DataTable({
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
</script>
<script>
  $('#submit_form').click(function() {
    let COMMENTAIRE = $('#COMMENTAIRE').val()
    let ID_DEMANDE = $('#ID_DEMANDE').val()
    let CURRENT_STEP = $('#CURRENT_STEP').val()
    let ACTION_ID = $('#ACTION_ID').val()
    let FORM_ID = $('#addCommentaire input#form_id').val();

    if (ID_DEMANDE == '' || CURRENT_STEP == '' || ACTION_ID == '' || FORM_ID == '') {
      $('#addCommentaire .modal-body').append(`
        <div class="text-danger my-3"><?= lang('messages_lang.message_erreur_commentaire') ?></div>
      `)

      return false;
    }


    $('#FORM_COMMENTAIRE').val(COMMENTAIRE);

    $(this).attr('disabled', 'disabled')

    $('#' + FORM_ID).submit()
  })

  $('div #openModal').click(function() {
    let ID = $(this).prev().attr('id')
    $('#addCommentaire input#form_id').val(ID);
  })



  function get_doc(doc) {
    if (doc == 1) {
      $('#pdf1').css('display', 'block');
      $('#modal').modal('show');
    } else {
      $('#modal').modal('hide');
      $('#pdf1').css('display', 'none');
    }
    if (doc == 2) {
      $('#pdf2').css('display', 'block');
      $('#modal').modal('show');
    } else {
      $('#modal').modal('hide');
      $('#pdf2').css('display', 'none');
    }
  }
</script>

<script>
  function get_doc_reference(id) {
    $.ajax({
      url: "<?= base_url() ?>/pip/Processus_Investissement_Public/get_doc_reference/" + id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
        const embed = document.getElementById('document');
        embed.setAttribute("src", "<?= base_url() ?>/" + data.DOC_REFERENCE);
        $('#doc_reference').modal('show');
      }
    });
  }
</script>

<script>
  function lieu_intervention() {
    var ID_DEMANDE_INFO_SUPP = $('#ID_DEMANDE_INFO_SUPP').val();
    var row_count = "1000000";
    $("#mytable_lieu").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('pip/Processus_Investissement_Public/lieu_intervention/') ?>",
        type: "POST",
        data: {
          ID_DEMANDE_INFO_SUPP: ID_DEMANDE_INFO_SUPP
        }
      },

      lengthMenu: [
        [10, 50, 100, row_count],
        [10, 50, 100, "All"]
      ],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],

      dom: 'Bfrtlip',
      order: [],
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  }
</script>


<script>
  function modal_comm(id, ids) {
    $.ajax({
      url: "<?= base_url() ?>/pip/Processus_Investissement_Public/getcommunes/" + id + '/' + ids,
      type: "GET",
      dataType: "JSON",

      success: function(data) {
        $('#communes').html(data.html);
        $('#province').html(data.PROVINCE);
        $('#detail_comm').modal('show');
      }
    });
  }
</script>
<script>
  function supprimerLieu(id, province, element) {
    $.ajax({
      url: '/pip/Processus_Investissement_Public/demande/delete/lieu/cible',
      type: "POST",
      data: {
        id: id,
      },
      success: function(response) {
        $("#" + element).remove()

        let tab_lieu = $('#mytable_lieu tbody').children()

        for (let i = 0; i < tab_lieu.length; i++) {
          let tds = tab_lieu[i].childNodes
          let communeCount = 0

          if (tds[1].innerText == province) {
            communeCount = parseInt(tds[3].innerText)
            tds[3].childNodes[0].innerText = communeCount - 1
          }

          if ((communeCount - 1) == 0) {
            document.querySelector('#mytable_lieu tbody').removeChild(tab_lieu[i])
            $('#detail_comm').modal('hide');
          }
        }

        if ($('#mytable_lieu tbody').children().length == 0) {
          $('#mytable_lieu').addClass('d-none')
        }
      },
      error: function(error) {
        console.error(error)
      }
    });

    return false
  }
</script>

<div class="modal fade" id="detail_comm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.labelle_commune_province') ?> <i style="color:blue;" id="province"></i></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row col-12" id="communes">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-close" aria-hidden="true"></i> <?= lang('messages_lang.label_ferm') ?></button>
      </div>
    </div>
  </div>
</div>