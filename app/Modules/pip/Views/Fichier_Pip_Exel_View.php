<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js">
  </script>
  <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js">
  </script>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <br>
                <div class="col-12 d-flex">

                  <div class="col-9" style="float: left;">
                    <h1 class="header-title text-dark">
                      <?=lang('messages_lang.FICHE_PREPARATION_PROJETS_INVESTISSEMENT_PUBLIC_AXEE_SUR_RESULTATS')?>
                    </h1>
                  </div>
                  <div class="col-3" style="float: right;">
                    <div style="float:right;">
                      <a class="btn btn-primary" href="<?= base_url('pip/Fichier_Pip_Exel/action/'.$donne_excel['ID_DEMANDE_INFO_SUPP'])?>"><?=lang('messages_lang.Ouvrir_excel')?></a>

                    </div>
                  </div>
                </div>
                <br>

                <div class="card-body">
                  <div style="margin-left: 15px" class="row">
                    <?php
                    if (session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="w-100 bg-success text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                      <?php
                    }
                    ?>
                  </div>

                  <div class="table-responsive container ">
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th width="20%"><?=lang('messages_lang.labelle_rubrique')?></th>
                          <th width="80%" colspan="9"><?=lang('messages_lang.labelle_description_projet')?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <th><?=lang('messages_lang.th_statut_projet')?>:</th>
                          <td colspan="9"><?= $donne_excel['DESCR_STATUT_PROJET'] ?></td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.labelle_nom_du_projet')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['NOM_PROJET'] ?></td>
                          <th width="10%"><?=lang('messages_lang.labelle_numero_projet')?></th>
                          <td colspan="5"></td>
                        </tr>

                        <!-- --------- definition de la largeur pour DATE DEBUT----------------- -->
                        <tr>
                          <th><?=lang('messages_lang.labelle_duree_projet')?></th>
                          <td colspan="1"> <?= $donne_excel["DATE_DEBUT_PROJET"] ?> </td>
                          <td colspan="2"> <?= $donne_excel["DATE_FIN_PROJET"] ?></td>
                          <td> <?=lang('messages_lang.labelle_duree')?> </td>
                          <td colspan="5"><?= $donne_excel["DUREE_PROJET"] ?></td>
                        </tr>

                        <tr>
                          <th> <?=lang('messages_lang.Est_ce_que_projet_se_realiser_au_niveau_national')?> </th>
                          <td colspan="9"> <?= $donne_excel["EST_REALISE_NATIONAL"] == 1 ? "oui" : "non" ?> </td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.Lieu_intervation_projet')?></th>
                          <th><?=lang('messages_lang.labelle_provinces')?></th>
                          <td  colspan="2"> 
                            <?php
                            if(!empty($province_commune_lie))
                            {
                              foreach ($province_commune_lie as $province)
                              {
                                ?>
                                <ul>
                                  <li> <?= $province->PROVINCE_NAME ?> </li>
                                </ul>
                                <?php
                              }
                            }
                            else
                            {
                              ?>
                              <?=lang('messages_lang.Toutes_provinces')?>
                              <?php
                            }
                            ?>
                          </td>
                          <th><?=lang('messages_lang.labelle_communes')?></th>
                          <td colspane="5">
                            <?php
                            if(!empty($province_commune_lie))
                            {
                              foreach ($province_commune_lie as $commune)
                              {
                                ?>
                                <ul>
                                  <li> <?= $commune->COMMUNE_NAME ?> </li>
                                </ul>
                                <?php
                              }
                            }
                            else
                            {
                              ?>
                              <?=lang('messages_lang.Toutes_communes')?>
                              <?php
                            }
                            ?>
                          </td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.Secteur_intervention_projet')?>:</th>
                          <td width="80%" colspan="9"> <?= $donne_excel['DESCR_SECTEUR'] ?> </td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.labelle_ministere_tutelle')?>:</th>
                          <td width="80%" colspan="9"><?= $donne_excel['DESCRIPTION_INSTITUTION'] ?></td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.labelle_projet_pilier')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['DESCR_PILIER'] ?></td>
                          <th><?=lang('messages_lang.labelle_n_pilier')?></th>
                          <td colspan="5"><?=$donne_excel['NUMERO_PILIER']?></td>
                        </tr>

                        <!-- -----------------DEBUT OBJECTIF STRATEGIQUE-------------------- -->
                        <tr>
                          <th><?=lang('messages_lang.labelle_objectif_str')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['DESCR_OBJECTIF_STRATEGIC'] ?></td>
                          <th><?=lang('messages_lang.labelle_num_objectif_str')?></th>
                          <td colspan="5"><?= $donne_excel['NUMERO_OBJECT_STRATEGIC'] ?></td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.labelle_objectif_pnd')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['DESCR_OBJECTIF_STRATEGIC_PND'] ?></td>
                          <th><?=lang('messages_lang.No_de_l_OS')?></th>
                          <td colspan="5"><?= $donne_excel['NUMERO_OBJCTIF_STRATEGIC_PND'] ?></td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.Axe_intervention_pnd_auquel_ratache_projet')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['DESCR_AXE_INTERVATION_PND'] ?></td>
                          <th><?=lang('messages_lang.No_de_AI')?></th>
                          <td colspan="5"><?= $donne_excel['NUM_AXE_INTERVENTION_PND'] ?></td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.Programme_budgetaire_du_ministere_auquel_ratache_projet')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['INTITULE_PROGRAMME'] ?></td>
                          <th><?=lang('messages_lang.labelle_num_program_budget')?></th>
                          <td colspan="5"><?= $donne_excel['CODE_PROGRAMME'] ?></td>
                        </tr>

                        <tr>
                          <th><?=lang('messages_lang.action_laquelle_ratache_projet')?>:</th>
                          <td width="35%" colspan="3"><?= $donne_excel['LIBELLE_ACTION'] ?></td>
                          <th><?=lang('messages_lang.labelle_num_action_projet')?></th>
                          <td colspan="5"><?= $donne_excel['CODE_ACTION'] ?></td>
                        </tr>

                        <!------------Etude et Document de reference ------------------------------ -->
                        <tr>
                          <th rowspan="<?=$count_document_reference?>"><?=lang('messages_lang.labelle_etude_document')?></th>
                          <?php
                          foreach ($document_reference as $doc)
                          {
                            ?>
                            <td><?= $doc->TITRE_ETUDE ?></td>
                            <td width="10"> <?= $doc->DOC_REFERENCE ?></td>
                            <td><?= $doc->DATE_REFERENCE ?></td>
                            <td><?= $doc->AUTEUR_ORGANISME ?></td>
                            <td colspan="5"><?= $doc->OBSERVATION ?></td>
                          </tr>
                            <?php
                          }
                          ?>
                          <tr>
                            <th><?=lang('messages_lang.labelle_contexte_justification')?>:</thth>
                            <td colspan="9"><?= $donne_excel['PATH_CONTEXTE_JUSTIFICATION'] ?></td>
                          </tr>

                          <!--------OBJECTIF GENERALE ET SPECIFIQUE -------------------- -->
                          <tr>
                            <th><?=lang('messages_lang.labelle_obj_general')?>:</th>
                            <td colspan="9"><?=$donne_excel['OBJECTIF_GENERAL'] ?></td>
                          </tr>

                          <tr>
                            <td rowspan="<?= $count_objectif ?>"><?=lang('messages_lang.labelle_objectif_specifique')?>:</td>
                            <?php
                            foreach ($specifique as $obj)
                            {
                              ?>
                              <td colspan="9"><?= $obj->DESCR_OBJECTIF ?></td>
                              <?php
                            }
                            ?>
                            <!-- ---------fin objectif specifique--------------- -->
                            <tr>
                              <th rowspan="<?= $count_livrable ?>"><?=lang('messages_lang.labelle_livrable_extrants')?>:</td>
                                <?php
                                foreach ($livrable as $oliv) {
                                  ?>
                                  <td colspan="9"> <?= $oliv->DESCR_LIVRABLE ?></td>
                                </tr>

                                <?php
                              }
                              ?>
                              <tr>
                                <th><?=lang('messages_lang.labelle_beneficiaire')?>:</td>
                                  <td colspan="9"><?= $donne_excel['BENEFICIAIRE_PROJET'] ?></td>
                                </tr>
                                <tr>
                                  <th><?=lang('messages_lang.labelle_impact_env')?>:</td>
                                    <td colspan="9"><?= $donne_excel['IMPACT_ATTENDU_ENVIRONNEMENT'] ?></td>
                                  </tr>
                                  <tr>
                                    <th><?=lang('messages_lang.labelle_impact_genre')?>:</td>
                                      <td colspan="9"><?= $donne_excel['IMPACT_ATTENDU_GENRE'] ?></td>
                                    </tr>

                                    <!-- ---------------------PRINCIPAUX RISQUES------------------------ -->

                                    <tr>
                                      <th rowspan="<?= $count_risque ?>"><?=lang('messages_lang.labelle_risques_projet')?></td>
                                        <?php
                                        foreach ($risque as $ris) {
                                          ?>
                                          <td colspan="2"><?= $ris->NOM_RISQUE ?></td>
                                          <td colspan="7"><?= $ris->MESURE_RISQUE ?></td>
                                        </tr>
                                        <?php
                                      }
                                      ?>
                                      <!-- ------------ CADRE DES MESURES DES RESULTATS------------------------ -->

                                      <tr style="background:aliceblue">
                                        <th rowspan="<?= ($count_general)+2+($count_specifique)+2+($count_livrable)+3 ?>">Cadre de mesure des resultats</th>
                                        <th rowspan="2"><?=lang('messages_lang.Libelle_objectif_generale')?></td>
                                          <th rowspan="2"><?=lang('messages_lang.labelle_nom_indicateur')?></td>
                                            <th rowspan="2"><?=lang('messages_lang.Libelle_unite_de_mesure')?></td>
                                              <th rowspan="2"><?=lang('messages_lang.labelle_valeur_reference').'('.lang('label_annee').')'?></td>
                                                <th colspan="5"><?=lang('messages_lang.valeur_cible')?> </td>
                                                </tr>
                                                <tr style="background:aliceblue">
                                                  <th width="9%">An1</td>
                                                    <th width="9%">An2</td>
                                                      <th width="9%">An3</td>
                                                        <th><?=lang('messages_lang.Total_sur_durree_projet')?></td>
                                                          <th></th>
                                                        </tr>
                                                        <tr>
                                                          <td rowspan="<?= $count_general ?>"><?= $donne_excel['OBJECTIF_GENERAL'] ?></td>
                                                          <?php
                                                          foreach ($general_req as $cd) {
                                                            ?>
                                                            <td><?= $cd->INDICATEUR_MESURE ?></td>
                                                            <td><?= $cd->UNITE_MESURE ?></td>
                                                            <td><?= $cd->VALEUR_REFERENCE_ANNE ?></td>
                                                            <td><?= $cd->ANNE_UN ?></td>
                                                            <td><?= $cd->ANNE_DEUX ?></td>
                                                            <td><?= $cd->ANNE_TROIS ?></td>
                                                            <td><?= $cd->TOTAL_DURE_PROJET ?></td>
                                                            <td></td>
                                                          </tr>
                                                          <?php
                                                        }
                                                        ?>
                                                        <!-- ----------------- entete  liberer objectif SPECIFIQUE--------------------------- -->

                                                        <tr >
                                                          <th rowspan="2"><?=lang('messages_lang.Libelle_objectif_specifique')?></td>
                                                            <th rowspan="2"><?=lang('messages_lang.labelle_nom_indicateur')?></td>
                                                              <th rowspan="2"><?=lang('messages_lang.Libelle_unite_de_mesure')?></td>
                                                                <th rowspan="2"><?=lang('messages_lang.labelle_valeur_reference').'('.lang('label_annee').')'?></td>
                                                                  <th colspan="5"><?=lang('messages_lang.valeur_cible')?> </td>
                                                                  </tr>
                                                                  <tr >
                                                                    <th>An1</td>
                                                                      <th>An2</td>
                                                                        <th>An3</td>
                                                                          <th><?=lang('messages_lang.Total_sur_durree_projet')?></td>
                                                                            <td></td>
                                                                          </tr>
                                                                          <tr>
                                                                            <?php
                                                                            foreach ($specifique_req as $obj) {
                                                                              ?>
                                                                              <td rowspan="<?= $count_specifique ?>"><?= $obj->DESCR_OBJECTIF ?></td>
                                                                              <?php
                                                                              foreach ($specifique_req as $cd) {
                                                                                ?>
                                                                                <td><?= $cd->INDICATEUR_MESURE ?></td>
                                                                                <td><?= $cd->UNITE_MESURE ?></td>
                                                                                <td><?= $cd->VALEUR_REFERENCE_ANNE ?></td>
                                                                                <td><?= $cd->ANNE_UN ?></td>
                                                                                <td><?= $cd->ANNE_DEUX ?></td>
                                                                                <td><?= $cd->ANNE_TROIS ?></td>
                                                                                <td><?= $cd->TOTAL_DURE_PROJET ?></td>
                                                                                <td></td>

                                                                                <?php
                                                                              }
                                                                              ?>
                                                                            </tr>
                                                                            <?php
                                                                          }
                                                                          ?>
                                                                          <!-- ----------------- entete DES LIVRABLES--------------------------- -->
                                                                          <tr>
                                                                            <th rowspan="2"><?=lang('messages_lang.labelle_nom_livrable')?></td>
                                                                              <th rowspan="2"><?=lang('messages_lang.labelle_nom_indicateur')?></td>
                                                                                <th rowspan="2"><?=lang('messages_lang.Libelle_unite_de_mesure')?></td>
                                                                                  <th rowspan="2">
                                                                                  </td>
                                                                                  <th colspan="5"><?=lang('messages_lang.valeur_cible')?> </td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                    <th>An1</td>
                                                                                      <th>An2</td>
                                                                                        <th>An3</td>
                                                                                          <th><?=lang('messages_lang.Total_sur_durree_projet')?></td>
                                                                                            <th><?=lang('messages_lang.labelle_total_triennal')?></td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                              <tr>
                                                                                                <?php
                                                                                                foreach ($livrable_req as $obj) {
                                                                                                  ?>
                                                                                                  <td rowspan="<?= $count_specifique ?>"><?= $obj->DESCR_LIVRABLE ?></td>
                                                                                                  <?php
                                                                                                  foreach ($livrable_req as $cd) {
                                                                                                    ?>
                                                                                                    <td><?= $cd->INDICATEUR_MESURE ?></td>
                                                                                                    <td><?= $cd->UNITE_MESURE ?></td>
                                                                                                    <td><?= $cd->VALEUR_REFERENCE_ANNE ?></td>
                                                                                                    <td><?= $cd->ANNE_UN ?></td>
                                                                                                    <td><?= $cd->ANNE_DEUX ?></td>
                                                                                                    <td><?= $cd->ANNE_TROIS ?></td>
                                                                                                    <td><?= $cd->TOTAL_DURE_PROJET ?></td>
                                                                                                    <td></td>
                                                                                                  </tr>
                                                                                                  <?php
                                                                                                }
                                                                                                ?>
                                                                                                <?php
                                                                                              }
                                                                                              ?>
                                                                                              <!----row- 26 ----bujet du projet par livrable------------------------- -->

                                                                                              <tr>
                                                                                                <th rowspan="<?=($count_budget)+3?>"><?=lang('messages_lang.tab_bpl')?></th>
                                                                                                <th rowspan="2"><?=lang('messages_lang.nom_des_livrables_extrat')?></th>
                                                                                                <th rowspan="2"><?=lang('messages_lang.labelle_cout_unitaire_livrable')?></th>
                                                                                                <th colspan="2"><?=lang('messages_lang.labelle_nomenclature')?></th>
                                                                                                <th colspan="5"><?=lang('messages_lang.budget_en_franc_burundais')?></th>
                                                                                              </tr>
                                                                                              <tr>
                                                                                                <th><?=lang('messages_lang.labelle_nom')?></th>
                                                                                                <th><?=lang('messages_lang.code')?></th>
                                                                                                <th>An1</th>
                                                                                                <th>An2</th>
                                                                                                <th>An3</th>
                                                                                                <th><?=lang('messages_lang.Total_sur_durree_projet')?></th>
                                                                                                <th><?=lang('messages_lang.labelle_total_triennal')?></th>
                                                                                              </tr>
                                                                                              <!-- ----------------------------indiquer le nom livrable------------------- -->

                                                                                              <tr>
                                                                                                <?php
                                                                                                foreach ($budget_req as $bg) {
                                                                                                  ?>
                                                                                                  <td rowspan="2"><?= $bg->DESCR_LIVRABLE ?></td>
                                                                                                  <td rowspan="2"><?= $bg->COUT_UNITAIRE_BIF ?></td>
                                                                                                  <th colspan="2">Cout d'atteindre les cibles</td>
                                                                                                    <td></td>
                                                                                                    <td></td>
                                                                                                    <td></td>
                                                                                                    <td></td>
                                                                                                    <td></td>
                                                                                                  </tr>
                                                                                                  <tr>
                                                                                                    <?php
                                                                                                    foreach ($budget_req as $bg) {
                                                                                                      ?>
                                                                                                      <td><?= $bg->DESCR_NOMENCLATURE ?></td>
                                                                                                      <td><?= $bg->CODE_NOMENCLATURE ?></td>
                                                                                                      <td><?= $bg->ANNE_UN ?></td>
                                                                                                      <td><?= $bg->ANNE_DEUX ?></td>
                                                                                                      <td><?= $bg->ANNE_TROIS ?></td>
                                                                                                      <td><?= $bg->TOTAL_DUREE_PROJET ?></td>
                                                                                                      <td><?= $bg->TOTAL_TRIENNAL ?></td>

                                                                                                      <?php
                                                                                                    }?>
                                                                                                    <?php
                                                                                                  }
                                                                                                  ?>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                  <th><?=lang('messages_lang.label_droit_taux')?> (BIF)</td>
                                                                                                    <th><?=lang('messages_lang.Indiquer_taux_change_pour_pricipales_devises')?></td>
                                                                                                      <th colspan="3">Euro=<?= $donne_excel['TAUX_CHANGE_EURO'] ?></td>
                                                                                                        <th colspan="5">usd=<?= $donne_excel['TAUX_CHANGE_USD'] ?></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                          <td><?=lang('messages_lang.observation_complementaire')?>:</td>
                                                                                                          <td colspan="9"></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                          <td><?=lang('messages_lang.date_préparation_fiche_projet')?>:</td>
                                                                                                          <td colspan="9"></td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                          <td><?=lang('messages_lang.labelle_responsable_projet')?>:</td>
                                                                                                          <td colspan="2"></td>
                                                                                                          <td colspan="2"></td>
                                                                                                          <td colspan="5"></td>
                                                                                                        </tr>
                                                                                                      </tbody>
                                                                                                    </table>
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