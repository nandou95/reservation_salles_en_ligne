<!DOCTYPE html>
<html lang="fr">

<head>
  <?php
  echo view('includesbackend/header.php');
  $validation = \Config\Services::validation();
  $session  = \Config\Services::session();
  $gdc = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
  ?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <style type="text/css">
    .table {
      background: none !important;
    }
    .modal-signature {
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      border-bottom-right-radius: .3rem;
      border-bottom-left-radius: .3rem
    }
  </style>
  <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="margin-top: -25px;" class="card">
                <div class="col-12">
                <?php if ($fichier !== null && $total_pour_tous_nomenclature !== null && $id !== null & $titre !== null && $pip_livrables_tables !== null && $pip_nomenclature_budgetaire !== null && $demande_source_financements !== null && $nom_du_livrables !== null && $user_connecter !== null && $risques !== null && $livrables !== null && $document_references !== null && $province_commune_lie !== null): ?>
                  <div class="mt-3 mb-2">
                    <a href="<?= base_url() ?>/pip/presentation_fichier_investisement_public/liste_projet" class="btn btn-primary"> <?= lang('messages_lang.retour_liste_payement_button') ?> </a>
                    <!-- <a href="<?= base_url() ?>/pip/Fichier_Pip_Exel/action/<?= $id ?>" class="btn btn-primary" style="float: right"> <?= lang('messages_lang.bouton_exporter') ?> </a> -->
                    <button class="btn btn-primary" id="sheetjsexport" style="float: right"><?= lang('messages_lang.bouton_exporter') ?></button>
                    </div>
                    <?php endif ?>
                  <div class="container-xxl py-2 subpage_bg" style="height: 85vh;overflow: scroll;">
                  <?php if ($fichier !== null && $total_pour_tous_nomenclature !== null && $id !== null & $titre !== null && $pip_livrables_tables !== null && $pip_nomenclature_budgetaire !== null && $demande_source_financements !== null && $nom_du_livrables !== null && $user_connecter !== null &&$risques !== null && $livrables !== null && $document_references !== null && $province_commune_lie !== null): ?>
                    <table class="table table-bordered" id="TableToExport">
                      <tr>
                        <th> <?= lang('messages_lang.labelle_rubrique') ?> </th>
                        <th colspan="9" class="text-center"> <?= lang('messages_lang.labelle_description_projet') ?> </th>
                      </tr>

                      <tr>
                        <th> <?= lang('messages_lang.labelle_statut_du_projet') ?> </th>
                        <td colspan="9"> <?= $fichier["DESCR_STATUT_PROJET"] ?> </td>
                      </tr>

                      <tr>
                        <th> <?= lang('messages_lang.labelle_nom_du_projet') ?> </th>
                        <td colspan="3"> <?= $fichier["NOM_PROJET"] ?> </td>
                        <th> <?= lang('messages_lang.labelle_numero_projet') ?> </th>
                        <td colspan="5"> <?= $fichier["NUMERO_PROJET"] ?></td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_duree_projet') ?></th>
                        <td> <?= $fichier["DATE_DEBUT_PROJET"] ?> </td>
                        <td colspan="2"> <?= $fichier["DATE_FIN_PROJET"] ?></td>
                        <th><?= lang('messages_lang.labelle_duree') ?> </th>
                        <td colspan="5"> <?= $fichier["DUREE_PROJET"] ?> </td>
                      </tr>

                      <tr>
                        <th> <?= lang('messages_lang.question_lieu_intervention') ?> </th>
                        <td colspan="9"> <?= $fichier["EST_REALISE_NATIONAL"] == 1 ? lang('messages_lang.label_oui') : lang('messages_lang.label_non') ?> </td>
                      </tr>

                      <tr>
                        <th rowspan="<?= count($province_province_lie) + 1 ?>"><?= lang('messages_lang.tab_lieu_intervention') ?></th>
                        
                        <?php if(empty($province_province_lie)): ?>
                            <td colspan="9">-</td>
                        <?php endif; ?>
                      </tr>
                        <?php foreach ($province_province_lie as $province): ?>
                          <tr>
                            <td colspan="4"><?= $province->PROVINCE_NAME ?></td>
                            <td colspan="5">
                                <?php foreach ($province_commune_lie[$province->ID_PROVINCE] as $commune): ?>
                                  <ul>
                                    <li> <?= $commune->COMMUNE_NAME ?> </li>
                                  </ul>
                                <?php endforeach ?>
                            </td>
                          </tr>
                          <?php endforeach ?>
                      <tr>
                        <th><?= lang('messages_lang.labelle_ministere_tutelle') ?></th>
                        <td colspan="9"> <?= $fichier["DESCRIPTION_INSTITUTION"] ?> </td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_projet_pilier') ?></th>
                        <td colspan="3"> <?= $fichier["DESCR_PILIER"] ?> </td>
                        <th><?= lang('messages_lang.labelle_n_pilier') ?></th>
                        <td> <?= $fichier["NUMERO_PILIER"] ?> </td>
                        <td colspan="4"></td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_objectif_str') ?></th>
                        <td colspan="3"> <?= $fichier["DESCR_OBJECTIF_STRATEGIC"] ?> </td>
                        <th><?= lang('messages_lang.labelle_num_objectif_str') ?></th>
                        <td> <?= $fichier["NUMERO_OBJECT_STRATEGIC"] ?> </td>
                        <td colspan="4"></td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_objectif_pnd') ?></th>
                        <td colspan="3"> <?= $fichier["DESCR_OBJECTIF_STRATEGIC_PND"] ?> </td>
                        <th> <?= lang('messages_lang.labelle_num_objectif_pnd') ?> </th>
                        <td> <?= $fichier["NUMERO_OBJCTIF_STRATEGIC_PND"] ?> </th>
                        <td colspan="4"></td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_axe_projet') ?></th>
                        <td colspan="3"> <?= $fichier["DESCR_AXE_INTERVATION_PND"] ?> </td>
                        <th> <?= lang('messages_lang.labelle_num_axe_projet') ?> </th>
                        <td> <?= $fichier["NUM_AXE_INTERVENTION_PND"] ?> </td>
                        <td colspan="4"></td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_program_budget') ?></th>
                        <td colspan="3"> <?= $fichier["INTITULE_PROGRAMME"] ?> </td>
                        <th><?= lang('messages_lang.labelle_num_program_budget') ?></th>
                        <td> <?= $fichier["CODE_PROGRAMME"] ?> </td>
                        <td colspan="4"></td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_action_projet') ?></th>
                        <td colspan="3"> <?= $fichier["LIBELLE_ACTION"] ?> </td>
                        <th><?= lang('messages_lang.labelle_num_action_projet') ?></th>
                        <td> <?= $fichier["CODE_ACTION"] ?> </td>
                        <td colspan="4"></td>
                      </tr>

                      <tr>
                        <th rowspan="<?= !empty($document_references) ? count($document_references) + 1 : 2 ?>"> <?= lang('messages_lang.labelle_etude_document') ?> </th>

                        <?php if (!empty($document_references)): ?>
                          <?php foreach ($document_references as $document_reference): ?>
                          <tr>
                            <td colspan="3"> <?= $document_reference->TITRE_ETUDE ?> / <?= $document_reference->DOC_REFERENCE ?> </td>
                            <td> <?= $document_reference->DATE_REFERENCE ?> </td>
                            <td> <?= $document_reference->AUTEUR_ORGANISME ?> </td>
                            <td> <?= $document_reference->OBSERVATION ?> </td>
                            <td colspan="3"></td>
                          </tr>
                          <?php endforeach ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="8"> N/A </td>
                          </tr>
                        <?php endif ?>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_contexte_justification') ?></th>
                        <td colspan="9"> <?= $fichier["PATH_CONTEXTE_JUSTIFICATION"] ?>  </td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_obj_general') ?></th>
                        <td colspan="9"> <?= $fichier["OBJECTIF_GENERAL"] ?> </td>
                      </tr>
                      <tr>
                        <th rowspan="<?= count($livrables) + 1 ?>"><?= lang('messages_lang.labelle_obj_specific') ?></th>
                        <?php foreach ( $livrables as $livrable): ?>
                        <tr>
                          <td colspan="9"> <?= $livrable->OBJECTIF_SPECIFIQUE ?> </td>
                        </tr>
                        <?php endforeach ?>
                      </tr>

                      <tr>
                        <th rowspan="<?= count($livrables) + 1 ?>"><?= lang('messages_lang.labelle_livrable_projet') ?></th>
                        <?php foreach ( $livrables as $livrable): ?>
                        <tr>
                          <td colspan="9"> <?= $livrable->DESCR_LIVRABLE ?> </td>
                        </tr>
                        <?php endforeach ?>
                      </tr>

                      <tr>
                        <th> <?= lang('messages_lang.labelle_beneficiaire') ?> </th>
                        <td colspan="9"> <?= $fichier["BENEFICIAIRE_PROJET"] ?> </td>
                      </tr>

                      <tr>
                        <th> <?= lang('messages_lang.labelle_impact_env') ?> </th>
                        <td colspan="9"> <?= $fichier["IMPACT_ATTENDU_ENVIRONNEMENT"] ?> </td>
                      </tr>

                      <tr>
                        <th> <?= lang('messages_lang.labelle_impact_genre') ?> </th>
                        <td colspan="9"> <?= $fichier["IMPACT_ATTENDU_GENRE"] ?> </td>
                      </tr>

                      <tr>
                        <th rowspan="<?= count($risques) + 1 ?>"><?= lang('messages_lang.labelle_risques_projet') ?></th>

                        <?php foreach ($risques as $risque) : ?>
                        <tr>
                          <td colspan="4"> <?= $risque->NOM_RISQUE ?> </td>
                          <td colspan="5"> <?= $risque->MESURE_RISQUE ?></td>
                        </tr>
                        <?php endforeach ?>
                      </tr>

                      <!-- ####################### Seconde Partie ################################# -->

                      <!-- Cadre de messure resultat -->
                      <tr>
                        <th rowspan="<?= count($pip_livrables_tables) + 4 ?>"> <?= lang('messages_lang.tab_cmr') ?>: </th>
                        <tr>
                          <th rowspan="3"><?= lang('messages_lang.labelle_nom_livrable') ?></th>
                          <th rowspan="3"><?= lang('messages_lang.labelle_nom_indicateur') ?></th>
                          <th rowspan="3"><?= lang('messages_lang.labelle_unite_mesure') ?></th>
                          <th rowspan="<?= count($pip_livrables_tables) + 3 ?>"></th>
                          <tr>
                            <th colspan="5"> <?= lang('messages_lang.labelle_valeur_cible') ?> </th>
                            <tr>
                              <?php foreach ($all_annee_budgetaires as $annees): ?>
                                <th><?= $annees->ANNEE_DESCRIPTION ?></th>
                              <?php endforeach ?>
                              <th><?= lang('messages_lang.labelle_total_duree') ?></th>
                              <th><?= lang('messages_lang.labelle_total_triennal') ?></th>
                            </tr>
                          </tr>
                        </tr>

                        <?php foreach($pip_livrables_tables as $key => $value): ?>
                          <tr> 
                            <td> <?= $key ?> </td>
                            <td> <?= $value[0]->INDICATEUR_MESURE ?? '-' ?> </td>
                            <td> <?= $value[0]->UNITE_MESURE ?? '-' ?> </td>
                            <td> <?= $value[0]->VALEUR_ANNEE_CIBLE ?? '-' ?> </td>
                            <td> <?= $value[1]->VALEUR_ANNEE_CIBLE ?? '-' ?> </td>
                            <td> <?= $value[2]->VALEUR_ANNEE_CIBLE ?? '-' ?> </td>
                            <td> <?= $value[0]->TOTAL_DURE_PROJET ?? '-' ?> </td>
                            <td> <?= $value[0]->TOTAL_TRIENNAL ?? '-' ?> </td>
                          </tr>
                        <?php endforeach ?>
                      </tr>

                      <!-- Fin cadre de messure resultat -->
                      <!-- Budget du projet par livrable -->

                      <tr>
                        <tr>
                          <th rowspan="<?= (count($livrables) + 1) * 7 + 2 ?>"><?= lang('messages_lang.tab_bpl') ?></th>
                          <th rowspan="2"><?= lang('messages_lang.labelle_nom_livrable') ?></th>
                          <th rowspan="2"><?= lang('messages_lang.labelle_cout_unitaire_livrable') ?></th>
                          <th colspan="2"><?= lang('messages_lang.labelle_nomenclature') ?></th>
                          <th colspan="4"><?= lang('messages_lang.labelle_budget') ?></th>
                        </tr>

                        <tr>
                          <th><?= lang('messages_lang.labelle_nom_geo') ?></th>
                          <th><?= lang('messages_lang.code_ptba_programme') ?></th>
                          <?php foreach ($all_annee_budgetaires as $annees): ?>
                            <th><?= $annees->ANNEE_DESCRIPTION ?></th>
                          <?php endforeach ?>
                          <th><?= lang('messages_lang.labelle_total_duree') ?></th>
                          <th><?= lang('messages_lang.labelle_total_triennal') ?></th>
                        </tr>

                        <?php foreach($livrables as $livrable): ?>
                          <tr>
                            <td rowspan="7"><?= $livrable->DESCR_LIVRABLE ?></td>
                            <td rowspan="7"><?= $livrable->COUT_LIVRABLE ?></td>
                          </tr>
                          <tr>
                            <th colspan="2"><?= lang('messages_lang.labelle_cout_cible') ?></th>
                            <td><?= $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] ?></td>
                            <td><?= $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] ?></td>
                            <td><?= $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                            <td>0</td>
                            <td><?= $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] + $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] + $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                          </tr>
                          <?php foreach($nomenclatures as $nomen): 
                              $montants = [];
                            
                              for($i=0; $i < count($all_annee_budgetaires);$i++){
                                $montants[$all_annee_budgetaires[$i]->ANNEE_BUDGETAIRE_ID] = 0;
                              }
                            
                              foreach($nom_du_livrables[$livrable->ID_DEMANDE_LIVRABLE] as $value){
                                if($nomen->DESCR_NOMENCLATURE == $value->DESCR_NOMENCLATURE){
                                  if($value->ANNEE_BUDGETAIRE_ID == $all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID){
                                      $montants[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] += $value->MONTANT_NOMENCALTURE;
                                  }
                                  if($value->ANNEE_BUDGETAIRE_ID == $all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID){
                                      $montants[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] += $value->MONTANT_NOMENCALTURE;
                                  }
                                  if($value->ANNEE_BUDGETAIRE_ID == $all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID){
                                    $montants[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] += $value->MONTANT_NOMENCALTURE;
                                  }
                                }
                              }
                          ?>
                            <tr>
                              <td><?= $nomen->DESCR_NOMENCLATURE ?></td>
                              <td><?= $nomen->CODE_NOMENCLATURE ?></td>
                              <td><?= $montants[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] ?></td>
                              <td><?= $montants[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] ?></td>
                              <td><?= $montants[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                              <td>0</td>
                              <td><?= $montants[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] + $montants[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] + $montants[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                            </tr>
                          <?php
                            endforeach; 
                            endforeach; ?>
                      </tr>
                      <tr>
                        <td rowspan="7"><?= lang('messages_lang.labelle_cout_projet') ?></td>
                        <td rowspan="7"><?= $total_livrable ?></td>
                      </tr>
                      <tr>
                        <th colspan="2"><?= lang('messages_lang.labelle_cout_cible') ?></th>
                        <td><?= $total_projet[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] ?></td>
                        <td><?= $total_projet[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] ?></td>
                        <td><?= $total_projet[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                        <td>0</td>
                        <td><?= $total_projet[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] + $total_projet[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] + $total_projet[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                      </tr>
                      <?php foreach($nomenclatures as $nomen): ?>
                        <tr>
                          <td><?= $nomen->DESCR_NOMENCLATURE ?></td>
                          <td><?= $nomen->CODE_NOMENCLATURE ?></td>
                          <td><?= $montants_total[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] ?></td>
                          <td><?= $montants_total[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] ?></td>
                          <td><?= $montants_total[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] ?></td>
                          <td>0</td>
                          <td><?= $montants_total[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] + $montants_total[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] + $montants_total[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] ?></td>
                        </tr>
                      <?php endforeach; ?>
                      <tr>
                        <th><?= lang('messages_lang.labelle_taux_change') ?></th>
                        <th><?= lang('messages_lang.labelle_taux_devise') ?></th>
                        <?php foreach($taux as $item): ?>
                        <td colspan="2"><?= $item->DEVISE ?> = </td>
                        <td colspan="2"><?= $item->TAUX ?></td>
                        <?php endforeach; ?>
                      </tr>
                      <tr>
                        <th rowspan="<?= count($demande_source_financements) + 6 ?>"><?= lang('messages_lang.labelle_financement_projet') ?>:</th>
                        <tr>
                          <th colspan="2" rowspan="2"><?= lang('messages_lang.labelle_nom_source_finance') ?></th>
                          <th rowspan="2"><?= lang('messages_lang.th_code_bailleur') ?></th>
                          <th colspan="6" class="text-center"><?= lang('messages_lang.labelle_valeur_nominale') ?></th>
                        </tr>
                        <tr>
                          <?php foreach ($all_annee_budgetaires as $annees): ?>
                            <th><?= $annees->ANNEE_DESCRIPTION ?></th>
                          <?php endforeach ?>
                          <th colspan="2"><?= lang('messages_lang.labelle_total_duree') ?></th>
                          <th><?= lang('messages_lang.labelle_total_triennal') ?></th>
                      </tr>
                      <?php foreach($demande_source_financements as $source): ?>
                      <tr>
                        <td colspan="2"><?= $source->NOM_SOURCE_FINANCE ?></td>
                        <td><?= $source->CODE_BAILLEUR ?></td>
                        <?php foreach($source_financement_valeur[$source->ID_DEMANDE_SOURCE_FINANCEMENT] as $value): ?>
                          <td><?= $value->SOURCE_FINANCEMENT_VALEUR_CIBLE ?></td>
                        <?php endforeach; ?>
                        <td colspan="2">0</td>
                        <td><?= $total_financement[$source->ID_DEMANDE_SOURCE_FINANCEMENT] ?></td>
                      </tr>
                      <?php endforeach; ?>
                      <tr>
                        <th colspan="3"><?= lang('messages_lang.labelle_total_finance_projet') ?></th>
                        <?php foreach($all_annee_budgetaires as $annees): ?>
                        <td><?= $total_financement_projet[$annees->ANNEE_BUDGETAIRE_ID] ?></td>
                        <?php endforeach; ?>
                        <td colspan="2">0</td>
                        <td><?= $total_total ?></td>
                      </tr>
                      <tr>
                        <th colspan="3"><?= lang('messages_lang.labelle_cout_projet') ?></th>
                        <td><?= $total_projet[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] ?></td>
                        <td><?= $total_projet[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] ?></td>
                        <td><?= $total_projet[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                        <td colspan="2">0</td>
                        <td><?= $total_projet[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] + $total_projet[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] + $total_projet[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID] ?></td>
                      </tr>
                      <tr>
                        <th colspan="3"><?= lang('messages_lang.labelle_gap') ?></th>
                        <?php foreach($all_annee_budgetaires as $annees): ?>
                        <td><?= $total_projet[$annees->ANNEE_BUDGETAIRE_ID] - $total_financement_projet[$annees->ANNEE_BUDGETAIRE_ID] ?></td>
                        <?php endforeach; ?>
                        <td colspan="2">0</td>
                        <td><?= ($total_projet[$all_annee_budgetaires[0]->ANNEE_BUDGETAIRE_ID] + $total_projet[$all_annee_budgetaires[1]->ANNEE_BUDGETAIRE_ID] + $total_projet[$all_annee_budgetaires[2]->ANNEE_BUDGETAIRE_ID]) - $total_total ?></td>
                      </tr>
                      <tr>
                        <th rowspan ="3"> <?= lang('messages_lang.tab_observation_complementaire') ?> : <br> <?= lang('messages_lang.labelle_date_presentation') ?> </th>
                      </tr>

                      <tr>
                        <td colspan="9"> <?= !empty($fichier["OBSERVATION_COMPLEMENTAIRE"]) ? $fichier["OBSERVATION_COMPLEMENTAIRE"] : 'N/A' ?> </td>
                      </tr>

                      <tr>
                        <td colspan="9"> <?= $fichier["DATE_PREPARATION_FICHE_PROJET"] ?> </td>
                      </tr>

                      <tr>
                        <th><?= lang('messages_lang.labelle_responsable_projet') ?></th>
                        <th colspan="3"> <?= $user_connecter["NOM"] ?> - <?= $user_connecter["PRENOM"] ?> </th>
                        <th colspan="2"> <?= $user_connecter["EMAIL"] ?> </th>
                        <th colspan="4"> 
                          <ul>
                              <li> <?= $user_connecter["TELEPHONE1"] ?>  </li>
                              <li> <?= $user_connecter["TELEPHONE2"] ?>  </li>
                          </ul>
                        </th>
                      </tr>
                    </table>
                    <script>
                      document.getElementById("sheetjsexport").addEventListener('click', function() {
                        /* Create worksheet from HTML DOM TABLE */
                        var wb = XLSX.utils.table_to_book(document.getElementById("TableToExport"));
                        // Format date début
                        const ws = wb.Sheets['Sheet1'];
                        ws['B4'].z = "yyyy-mm";
                        delete ws['B4'].w;
                        XLSX.utils.format_cell(ws['B4']);

                        // Format date fin
                        ws['C4'].z = "yyyy-mm";
                        delete ws['C4'].w;
                        XLSX.utils.format_cell(ws['C4']);

                        // Format date de préparation
                        ws['B69'].z = "yyyy-mm-dd hh:mm:ss";
                        delete ws['B69'].w;
                        XLSX.utils.format_cell(ws['B69']);
                        /* Export to file (start a download) */
                        XLSX.writeFile(wb, "<?= str_replace("'","",str_replace(' ','_',$fichier["NOM_PROJET"])) ?>.xlsx");
                      });
                    </script>
                      <?php else: ?>
                        <div style="height: 100%;display: flex;flex-direction: column;justify-content: center;align-items: center;">
                        <h3> <?= lang('messages_lang.labelle_empty_data') ?></h3>
                        <a href="<?= base_url() ?>/pip/presentation_fichier_investisement_public/liste_projet" class="btn btn-primary"> <?= lang('messages_lang.retour_liste_payement_button') ?> </a>
                        </div>
                      <?php endif ?>
                  </div>
                </div>
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
  <?php echo view('includesbackend/scripts_js.php'); ?>
</body>


</html>