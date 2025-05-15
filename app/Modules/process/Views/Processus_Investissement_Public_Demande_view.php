<!DOCTYPE html>
<html lang="en">
<head>
	<?php echo view('includesbackend/header.php'); ?>
	<style type="text/css">
		.help-block {
			color: red;
		}
		.res_scol {
			height: 340px;
			overflow-y: visible;
		}
		.required-field {
			font-size: 12px;
			opacity: .5;
		}
	</style>
</head>
<body>
	<div class="wrapper">
		<?php echo view('includesbackend/navybar_menu.php'); ?>
		<div class="main">
			<?php echo view('includesbackend/navybar_topbar.php'); ?>
			<main class="content">
				<div class="container-fluid">
					<div class="header">
						<h1 class="header-title"><?=$title?></h1>
					</div>
					<div class="row">
						<div class="col-12">
							<div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
								<div class="card-header">
									<div class="w-25"  style="margin-left:75%">
										<a href="<?= base_url("pip/Projet_Pip_Infini/liste_pip_infini")  ?>" class="btn btn-dark float-right"><i class="nav-icon fas fa-list"></i><?= lang('messages_lang.link_list') ?></a>
									</div>
								</div>
								<div class="card-body" style="overflow-x:auto;">
									<form id="mission" class="container js-formSubmit" enctype="multipart/form-data">
										<?php if(isset($oldValues)): ?>
											<input type="hidden" name="demande_id" id="demande_id" value="<?= $oldValues[0]->ID_DEMANDE_INFO_SUPP ?>">
										<?php endif; ?>
										<br>
										<ul class="nav nav-tabs" role="tablist">
											<li class="nav-item">
												<a class="nav-link " id="tab1" data-toggle="tab" href="#"><i class="fa fa-pencil" aria-hidden="true"></i> <?= lang('messages_lang.label_descr_profil') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link " id="tab10" data-toggle="tab" href="#">
												<?= lang('messages_lang.tab_lieu_intervention') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab2" data-toggle="tab" href="#">
												<?= lang('messages_lang.tab_etude_document') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab3" data-toggle="tab" href="#"><?= lang('messages_lang.tab_contexte_projet') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab4" data-toggle="tab" href="#"><?= lang('messages_lang.tab_impact_env') ?> </a>
											</li>

											<li class="nav-item">
												<a href="#" class="nav-link" id="tab14" data-toggle="tab"><?= lang('messages_lang.tab_risque_projet') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab13" data-toggle="tab" href="#"><?= lang('messages_lang.tab_cmr') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab7" data-toggle="tab" href="#"><?= lang('messages_lang.tab_bpl') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab8" data-toggle="tab" href="#"><?= lang('messages_lang.tab_sfp') ?></a>
											</li>

											<li class="nav-item">
												<a class="nav-link" id="tab11" data-toggle="tab" href="#"><?= lang('messages_lang.tab_observation_complementaire') ?></a>
											</li>
										</ul>
										<!-- Tab panes -->
										<div class="tab-content">
											<!-- Debut description (info principal du projet)  -->
											<div id="projet" class="container tab-pane"><br>
												<div class="row mb-3">
													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_statut_du_projet') ?></span> <font color="red">*</font></label>
														<select name="ID_STATUT_PROJET" id="ID_STATUT_PROJET" autofocus class="form-control">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>

															<?php foreach ($status as $item) : ?>
																<option value="<?= $item->ID_STATUT_PROJET ?>" <?php if(isset($oldValues) && $item->ID_STATUT_PROJET == $oldValues[0]->ID_STATUT_PROJET): ?> selected <?php endif; ?>>
																	<?= $item->DESCR_STATUT_PROJET ?>
																</option>
															<?php endforeach ?>
														</select>
													</div>

													<div class="col-md-6">
														<label for=""><span> <?= lang('messages_lang.labelle_nom_du_projet') ?> </span> <font color="red">*</font></label>
														<input type="text" name="NOM_PROJET" id="NOM_PROJET" class="form-control" max="100" value="<?= isset($oldValues[0]->NOM_PROJET) ? $oldValues[0]->NOM_PROJET : '' ?>" autofocus placeholder="<?= lang('messages_lang.placeholder_nom_projet') ?>">
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-4">
														<label for=""><span><?= lang('messages_lang.labelle_date_de_debut') ?></span> <font color="red">*</font></label>
														<input type="month" name="DATE_DEBUT_PROJET" id="DATE_DEBUT_PROJET" value="<?= isset($oldValues[0]->DATE_DEBUT_PROJET) ? $oldValues[0]->DATE_DEBUT_PROJET : '' ?>" class="form-control">
													</div>

													<div class="col-md-4">
														<label for=""><span><?= lang('messages_lang.labelle_date_de_fin') ?></span> <font color="red">*</font></label>
														<input type="month" min="<?= date('Y-m') ?>" name="DATE_FIN_PROJET" id="DATE_FIN_PROJET" value="<?= isset($oldValues[0]->DATE_FIN_PROJET) ? $oldValues[0]->DATE_FIN_PROJET : '' ?>" class="form-control">
													</div>

													<div class="col-md-4">
														<label for=""><?= lang('messages_lang.labelle_duree') ?></label>
														<input type="text" name="DUREE_PROJET" readonly class="form-control" value="<?= isset($oldValues[0]->DUREE_PROJET) ? $oldValues[0]->DUREE_PROJET : "" ?>" id="DUREE_PROJET">
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_axe_intervention_PND') ?></span> <font color="red">*</font></label>
														<select name="ID_AXE_INTERVENTION_PND" id="ID_AXE_INTERVENTION_PND" class="form-control select2">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach ($axe_intervations_pnd as $axe_intervation_pnd) : ?>
																<option value="<?= $axe_intervation_pnd->ID_AXE_INTERVENTION_PND ?>" <?php if(isset($oldValues) && $oldValues[0]->ID_AXE_INTERVENTION_PND == $axe_intervation_pnd->ID_AXE_INTERVENTION_PND): ?> selected <?php endif; ?>>
																	<?= $axe_intervation_pnd->DESCR_AXE_INTERVATION_PND ?>
																</option>
															<?php endforeach ?>
														</select>
													</div>

													<div class="col-md-6">
														<label for="INSTITUTION_ID"><span><?= lang('messages_lang.labelle_ministere_tutelle') ?> </span><font color="red">*</font></label>
														<select id="INSTITUTION_ID" name="INSTITUTION_ID" class="form-control select2">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach ($institutions as $institution) : ?>
																<option value="<?= $institution->INSTITUTION_ID ?>" <?php if(isset($oldValues) && $oldValues[0]->INSTITUTION_ID == $institution->INSTITUTION_ID): ?> selected <?php endif; ?>>
																	<span> <?= $institution->CODE_INSTITUTION . ' - ' . $institution->DESCRIPTION_INSTITUTION ?> </span>
																</option>
															<?php endforeach ?>
														</select>
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_pilier') ?></span> <font color="red">*</font></label>
														<select name="ID_PILIER" id="ID_PILIER" class="form-control select2">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach ($piliers as $pilier) : ?>
																<option value="<?= $pilier->ID_PILIER ?>" <?php if(isset($oldValues) && $oldValues[0]->ID_PILIER == $pilier->ID_PILIER): ?> selected <?php endif; ?>>
																	<?= $pilier->DESCR_PILIER ?>
																</option>
															<?php endforeach ?>
														</select>
													</div>

													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_objectif_strategique') ?></span> <font color="red">*</font></label>
														<select name="ID_OBJECT_STRATEGIQUE" id="ID_OBJECT_STRATEGIQUE" class="form-control select2">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach ($objectif_strategiques as $objectif_strategique) : ?>
																<option value="<?= $objectif_strategique->ID_OBJECT_STRATEGIQUE  ?>" <?php if(isset($oldValues) && $oldValues[0]->ID_OBJECT_STRATEGIQUE == $objectif_strategique->ID_OBJECT_STRATEGIQUE): ?> selected <?php endif; ?>>
																	<?= $objectif_strategique->DESCR_OBJECTIF_STRATEGIC ?>
																</option>
															<?php endforeach ?>
														</select>
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_objectif_strategique_PND') ?> </span> <font color="red">*</font></label>
														<select name="ID_OBJECT_STRATEGIC_PND" id="ID_OBJECT_STRATEGIC_PND" class="form-control select2">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach ($objectif_strategiques_pnd as $objectif_strategique_pnd) : ?>
																<option value="<?= $objectif_strategique_pnd->ID_OBJECT_STRATEGIC_PND ?>" <?php if(isset($oldValues) && $oldValues[0]->ID_OBJECT_STRATEGIC_PND == $objectif_strategique_pnd->ID_OBJECT_STRATEGIC_PND): ?> selected <?php endif; ?>>
																	<?= $objectif_strategique_pnd->DESCR_OBJECTIF_STRATEGIC_PND ?>
																</option>
															<?php endforeach ?>
														</select>
													</div>

													<div class="col-md-6">
														<label for="ID_PROGRAMME_PND"><span><?= lang('messages_lang.labelle_programme_prioritaire') ?></span> <font color="red">*</font></label>
														<select name="ID_PROGRAMME_PND" id="ID_PROGRAMME_PND" class="form-control select2">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach($programme_pnd as $programme): ?>
																<option value="<?= $programme->ID_PROGRAMME_PND ?>" <?php if(isset($oldValues[0]->ID_PROGRAMME_PND) && $oldValues[0]->ID_PROGRAMME_PND == $programme->ID_PROGRAMME_PND): ?> selected <?php endif; ?>><?= $programme->DESCR_PROGRAMME ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-6">
														<label><span><?= lang('messages_lang.labelle_programme_budget') ?></span> <font color="red">*</font></label>
														<select name="ID_PROGRAMME" id="ID_PROGRAMME" class="form-control select2 js-id-programe">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php if(isset($s_programmes)): 
																foreach($s_programmes as $programme):    
																	?>
																	<option value="<?= $programme->PROGRAMME_ID ?>" <?php if(isset($oldValues) && $programme->PROGRAMME_ID == $oldValues[0]->PROGRAMME_ID): ?> selected <?php endif; ?>><?= $programme->CODE_PROGRAMME ?> - <?= $programme->INTITULE_PROGRAMME ?></option>
																	<?php 
																endforeach;
															endif; ?>
														</select>
													</div>

													<div class="col-md-6 parent-js">
														<label for=""><span><?= lang('messages_lang.label_action') ?></span> <font color="red">*</font></label>
														<select name="ID_ACTION" id="ID_ACTION" class="form-control select2 actions-elements">
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php if(isset($proc_actions)): 
																foreach($proc_actions as $action):    
																	?> 
																	<option value="<?= $action->ACTION_ID ?>" <?php if(isset($oldValues) && $action->ACTION_ID == $oldValues[0]->ACTION_ID): ?> selected <?php endif; ?>><?= $action->CODE_ACTION.'-'.$action->LIBELLE_ACTION ?></option> 
																	<?php 
																endforeach;
															endif; ?>
														</select>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12">
														<?php if(isset($oldValues)): ?>
															<button type="button" id="btn_projet_update" style="display: none;" class="btn btn-primary float-right mb-3"><?= lang('messages_lang.labelle_mettre_a_jour') ?> <span id="loading_updt_projet"></button>
														<?php else: ?>
															<button type="button" id="btn_projet_save" class="btn btn-primary float-right mb-3"><?= lang('messages_lang.label_enre') ?> <span id="loading_sv_projet"></span></button>
														<?php endif; ?>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12" id="btn_projet">
														<button type="button" class="btn btn-info float-right" <?php if(!isset($oldValues)): ?> style="display: none;" disabled <?php endif; ?> id="js-validate-data"><?= lang('messages_lang.labelle_etape_suiv') ?></button>
													</div>
												</div>
											</div>
											<!-- Fin description (info principal du projet)  -->

											<!--Debut Lieu d'intervention -->
											<div id="lieu_intervention" class="container tab-pane">
												<div class="row d-flex align-items-center">
													<div class="pt-3 pb-3 ">
														<span><?= lang('messages_lang.question_lieu_intervention') ?></span>
													</div>
													<div class="ml-5">
														<div class="form-check form-check-inline">
															<input class="form-check-input radio-select" <?php if(isset($oldValues) && $oldValues[0]->EST_REALISE_NATIONAL == '1'): ?> checked <?php endif; ?> type="radio" name="EST_REALISE_NATIONAL" id="inlineRadio1" value="1">
															<label class="form-check-label" for="inlineRadio1"><?= lang('messages_lang.label_oui') ?></label>
														</div>
														<div class="form-check form-check-inline">
															<input class="form-check-input radio-select" <?php if(isset($oldValues) && $oldValues[0]->EST_REALISE_NATIONAL == '0'): ?> checked <?php endif; ?> type="radio" name="EST_REALISE_NATIONAL" id="inlineRadio2" value="0">
															<label class="form-check-label" for="inlineRadio2"><?= lang('messages_lang.label_non') ?></label>
														</div>
													</div>
												</div>

												<div class="row mb-3 <?= isset($oldValues) && $oldValues[0]->EST_REALISE_NATIONAL == '0' ? '' : 'd-none' ?> cartographie">
													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_provinces') ?></span> <font color="red">*</font></label>
														<select name="ID_PROVINCE" autofocus class="form-control select2" id="ID_PROVINCE" >
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															<?php foreach ($provinces as $province) : ?>
																<option value="<?= $province->PROVINCE_ID ?>">
																	<?= $province->PROVINCE_NAME ?>
																</option>
															<?php endforeach ?>
														</select>
													</div>

													<div class="col-md-6">
														<label for=""><span><?= lang('messages_lang.labelle_communes') ?></span> <font color="red">*</font></label>
														<select name="ID_COMMUNE[]" class="form-control select2" id="ID_COMMUNE" multiple>
															<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
															
														</select>
													</div>

													<div class="col-12 mt-3">
														<div class="row">
															<div class="col-12 mb-3" style="display: none;">
																<button type="button" class="btn btn-primary projet_save_button float-right"> <?= lang('messages_lang.bouton_ajouter') ?><span id="loading_store_lieu_inter"></span></button>
															</div>

															<div class="col-12">
																<div class="table-responsive">
																	<table id="mytable_lieu" class="table tab_projet <?php if (!isset($lieux) || (isset($lieux) && !$lieux)) : ?>d-none<?php endif; ?>">
																		<thead>
																			<tr>
																				<th><?= lang('messages_lang.labelle_provinces') ?></th>
																				<th><?= lang('messages_lang.labelle_communes') ?></th>
																			</tr>
																		</thead>
																		<tbody>
																			<?php
																			if(isset($lieux)): 
																				foreach($lieux as $key => $lieu):
																					?>
																					<tr>
																						<td><?= $key ?></td>
																						<td><a class="btn btn-primary" onclick="modal_comm(<?= $lieu->ID_PROVINCE ?>,<?= $oldValues[0]->ID_DEMANDE_INFO_SUPP ?>)"><?= $lieu->nbr_communes ?></a></td>
																					</tr>
																				<?php endforeach; 
																			endif;
																			?>
																		</tbody>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12" id="btn_lieu_intervention">
														<button type="button" class="btn btn-info float-left" id="lieu_intervention_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
														<button type="button" class="btn btn-info float-right" id="lieu_intervention_next" <?php if(!isset($oldValues)): ?>disabled<?php endif; ?>><?= lang('messages_lang.labelle_etape_suiv') ?></button>
													</div>
												</div>
											</div>
											<!-- Fin lieu d'intervention -->

											<!-- Debut etude de document -->
											<div id="tab_etude_document" class="container tab-pane">
												<div class="pt-3">
													<div class="form-group">
														<div class="row d-flex align-items-center">
															<?= lang('messages_lang.question_etude') ?>
															<div class="ml-5">
																<div class="form-check form-check-inline ml-5">
																	<input type="radio" id="A_UNE_ETUDE_OUI" name="A_UNE_ETUDE" <?php if(isset($oldValues[0]->A_UNE_ETUDE) && $oldValues[0]->A_UNE_ETUDE == '1'): ?> checked <?php endif; ?> value="1" class="etude_faisabilite form-check-input">
																	<label class="form-check-label" for="A_UNE_ETUDE_OUI"><?= lang('messages_lang.label_oui') ?></label>
																</div>
																<div class="form-check form-check-inline">
																	<input type="radio" id="A_UNE_ETUDE_NON" name="A_UNE_ETUDE" <?php if(isset($oldValues[0]->A_UNE_ETUDE) && $oldValues[0]->A_UNE_ETUDE == '0'): ?> checked <?php endif; ?>  value="0" class="etude_faisabilite form-check-input">
																	<label class="form-check-label" for="A_UNE_ETUDE_NON"><?= lang('messages_lang.label_non') ?></label>
																</div>
															</div>
														</div>
													</div>
												</div>

												<div id="etude_document_reference" <?php if((isset($oldValues[0]->A_UNE_ETUDE) && $oldValues[0]->A_UNE_ETUDE == '0') || !isset($oldValues[0]->A_UNE_ETUDE)): ?> style="display: none;" <?php endif; ?> class="row">
													<div class="card-body">
														<!-- debut statut -->
														<div class="row">
															<div class="statut_etude col-md-6">
																<div class="form-group">
																	<label ><?= lang('messages_lang.labelle_statut_etude') ?> <span style="color: red;">*</span></label>
																	<select name="statut_etud" id="id_statut_etud" autofocus class="form-control " onchange="get_statut()">
																		<option value="">-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<option value="1"><?= lang('messages_lang.select_valide') ?></option>
																		<option value="0"><?= lang('messages_lang.select_cours') ?></option>
																	</select>
																</div>
															</div>
														</div>
														<hr>
														<!-- fin statut -->
														<div class="row">
															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label><?= lang('messages_lang.labelle_titre_etude') ?> <font color="red">*</font></label>
																	<input type="text" name="TITRE_ETUDE" class="form-control" id="TITRE_ETUDE" value="<?= isset($references[0]->TITRE_ETUDE) ? $references[0]->TITRE_ETUDE: '' ?>">
																	<font color="red" id="error_TITRE_ETUDE"></font>
																	<div class="text-danger" id="TITRE_ETUDE">
																	</div>
																</div>
															</div>

															<div class="col-12 col-md-6" id="doc_reference">
																<div class="form-group">
																	<label class="form-label"><?= lang('messages_lang.labelle_document_reference') ?> <span style="color: red">*</span></label>
																	<?php if(isset($references[0]->DOC_REFERENCE) && !empty($references[0]->DOC_REFERENCE)): ?><span class="text-danger" style="font-size: 12px;"><?= explode('/',$references[0]->DOC_REFERENCE)[2] ?></span><?php endif; ?>
																	<span><input type="file" name="DOC_REFERENCE" id="DOC_REFERENCE" accept=".pdf" class="form-control"></span>
																	<div class="text-danger" id="DOC_REFERENCE"></div>
																</div>
																<div class="text-danger" id="DOC_REFERENCE"></div>
															</div>

															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label class="form-label"><?= lang('messages_lang.labelle_statut_juridique') ?> <span style="color: red">*</span></label>
																	<select class="form-control select2" name="STATUT_JURIDIQUE" id="STATUT_JURIDIQUE">
																		<option disabled selected>-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<option value="0"><?= lang('messages_lang.select_personne_morale') ?></option>
																		<option value="1"><?= lang('messages_lang.select_personne_physique') ?></option>
																	</select>
																</div>
															</div>

															<!-- debut -->
															<div class="col-12 col-md-6" style="display: none;" id="AUTEUR_ORGANISME_DIV">
																<div class="form-group">
																	<label class="form-label"><?= lang('messages_lang.labelle_nom_auteur') ?> <span style="color: red">*</span></label>
																	<input type="text" name="AUTEUR_ORGANISME" class="form-control" id="AUTEUR_ORGANISME" value="<?= isset($references[0]->AUTEUR_ORGANISME) ? $references[0]->AUTEUR_ORGANISME : '' ?>">
																</div>
															</div>

															<div class="col-12 col-md-6" style="display: none;" id="pays_auteur">
																<div class="form-group">
																	<label class="form-label" id="PAYS_ID"> <span style="color: red">*</span></label>
																	<select class="form-control select2" name="PAYS_ORIGINE" id="PAYS_ORIGINE">
																		<option disabled selected>-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<?php foreach ($countries as $pays):?>
																			<option value="<?= $pays->COUNTRY_ID ?>"> <?= $pays->CommonName ?> </option>
																		<?php endforeach ?>
																	</select>
																</div>
															</div>

															<div class="col-12 col-md-6" id="additional_fields_23" style="display:none" >
																<div class="form-group">
																	<label class="form-label" for="NIF_AUTEUR"><?= lang('messages_lang.labelle_NIF') ?>  <span style="color: red;">*</span></label>
																	<input type="text" class="form-control" name="NIF_AUTEUR" id="NIF_AUTEUR">
																</div>
															</div>

															<div class="col-12 col-md-6" id="additional_fields_24" style="display:none" >
																<div class="form-group">
																	<label class="form-label" for="REGISTRE_COMMERCIALE"><?= lang('messages_lang.labelle_registre_commerce') ?>  </label>
																	<input type="text" class="form-control" name="REGISTRE_COMMERCIALE" id="REGISTRE_COMMERCIALE">
																</div>
															</div>

															<div class="col-12 col-md-6" id="additional_fields_2" style="display:none">
																<div class="form-group">
																	<label class="form-label" for="adresse"><?= lang('messages_lang.labelle_adresse') ?> <span style="color: red;">*</span></label>
																	<input type="text" class="form-control" name="adresse_organisation" id="adresse_organisation_id">
																</div>
															</div>
															<!-- fin -->

															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label class="form-label"><?= lang('messages_lang.labelle_annee_etude') ?> <span style="color: red">*</span></label>
																	<input type="month"  name="DATE_REFERENCE" class="form-control" value="<?= isset($references[0]->DATE_REFERENCE) ? $references[0]->DATE_REFERENCE : '' ?>" id="DATE_REFERENCE">
																</div>
																<div class="text-danger" id="DATE_REFERENCE"></div>
															</div>

															<div class="col-12">
																<div class="form-group">
																	<div class="form-label"><?= lang('messages_lang.labelle_observartion') ?></div>
																	<textarea name="OBSERVATION" id="OBSERVATION" class="form-control"><?= isset($references[0]->OBSERVATION) ? $references[0]->OBSERVATION : '' ?></textarea>
																</div>
															</div>

															<div class="col-12 col-md-12">
																<button class="btn btn-primary ed_save_button" style="<?php if(isset($oldValues)): ?> display: none; <?php endif; ?> float: right;"> <?= lang('messages_lang.bouton_ajouter') ?> </button>
															</div>

															<div class="col-12">
																<table class="table etude_document <?php if(!isset($references) || (isset($references) && !$references)): ?> d-none <?php endif; ?>">
																	<thead>
																		<tr>
																			<th><?= lang('messages_lang.th_titre') ?></th>
																			<th><?= lang('messages_lang.document_action') ?></th>
																			<th><?= lang('messages_lang.labelle_annee_etude') ?></th>
																			<th><?= lang('messages_lang.labelle_statut_etude') ?></th>
																			<th><?= lang('messages_lang.th_statut_juridique') ?></th>
																			<th><?= lang('messages_lang.th_nom_auteur') ?></th>
																			<th><?= lang('messages_lang.labelle_observartion') ?></th>
																			<th></th>
																		</tr>
																	</thead>

																	<tbody>
																		<?php if(isset($references)): 
																			foreach($references as $r):    
																				?>
																				<tr>
																					<td><?= $r->TITRE_ETUDE ?></td>
																					<td><?= !empty($r->DOC_REFERENCE) ?'<button style="border:none;" type="button" onclick="get_doc(2,'.explode('/',$r->DOC_REFERENCE)[2].')"><span class="fa fa-file-pdf" style="color:#b30f0f;font-size: 200%;"></span></button>':'-'?></td>
																					<td><?= date_format(new \DateTime($r->DATE_REFERENCE), 'Y-m') ?></td>
																					<td><?= $r->STATUT_ETUDE == '0' ? lang('messages_lang.select_cours') : lang('messages_lang.select_valide') ?></td>
																					<td><?= $r->STATUT_JURIDIQUE == '0' ? lang('messages_lang.select_personne_morale') : lang('messages_lang.select_personne_physique') ?></td>
																					<td><?= $r->AUTEUR_ORGANISME ?></td>
																					<td><?= $r->OBSERVATION ?></td>
																					<td class="flex justify-content-end"><div id="supprimer_etude" data-id="<?= $r->ID_ETUDE_DOC_REF ?>" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
																				</tr>
																				<?php 
																			endforeach;
																		endif; ?>
																	</tbody>
																</table>
															</div>
														</div>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12">
														<button type="button" id="btn_piece" class="btn btn-info tab_etude_document_prev" style="float: left;"><?= lang('messages_lang.labelle_etape_prec') ?></button>
														<button type="button" id="btn_piece" class="btn btn-info tab_etude_document_next" style="float: right;" <?php if(!isset($oldValues) || $oldValues[0]->A_UNE_ETUDE == null): ?> disabled <?php endif; ?>><?= lang('messages_lang.labelle_etape_suiv') ?></button>
													</div>
												</div>
											</div>
											<!-- Fin etude de document -->

											<!-- Debut contexte du projet -->
											<div id="tab_contexte_projet" class="container tab-pane">
												<div class="row">
													<div class="card-body">
														<div class="row">
															<div class="col-12">
																<div class="row">
																	<div class="col-12">
																		<div class="form-group">
																			<label class="form-label"><?= lang('messages_lang.labelle_contexte_justification') ?> <span style="color: red">*</span></label>
																			<textarea name="PATH_CONTEXTE_JUSTIFICATION" id="PATH_CONTEXTE_JUSTIFICATION" class="form-control"><?= isset($oldValues[0]->PATH_CONTEXTE_JUSTIFICATION) ? $oldValues[0]->PATH_CONTEXTE_JUSTIFICATION : '' ?></textarea>
																		</div>
																	</div>
																</div>

																<div class="row">
																	<div class="col-12">
																		<div class="form-group">
																			<label class="form-label"><?= lang('messages_lang.labelle_objectif_general') ?> <span style="color: red">*</span></label>
																			<textarea name="OBJECTIF_GENERAL" id="OBJECTIF_GENERAL" class="form-control"><?= isset($oldValues[0]->OBJECTIF_GENERAL) ? $oldValues[0]->OBJECTIF_GENERAL : '' ?></textarea>
																		</div>
																	</div>
																</div>

																<div class="row">
																	<div class="col-12">
																		<div class="form-group">
																			<label for="Nom" class="form-label"><?= lang('messages_lang.labelle_objectif_specifique') ?> <span style="color: red">*</span></label>
																			<div class="input-group">
																				<textarea type="text" name="DESCR_OBJECTIF" id="DESCR_OBJECTIF" class="form-control"></textarea>
																				<button class="input-group-addon btn btn-secondary" style="display: none;" type="button" id="descr_objectif_save"><?= lang('messages_lang.bouton_ajouter') ?></button>
																			</div>
																		</div>
																	</div>    
																</div>

																<div class="row"> 
																	<div class="col-12 col-md-6">
																		<div class="form-group">
																			<label class="form-label"><?= lang('messages_lang.labelle_livrable_extrants') ?> <span style="color: red">*</span></label>
																			<div class="input-group">
																				<textarea name="DESCR_LIVRABLE" class="form-control" id="DESCR_LIVRABLE"></textarea>
																			</div>
																		</div>
																	</div>

																	<div class="col-12 col-md-6">
																		<div class="form-group">
																			<label for="COUT_LIVRABLE" class="form-label"><?= lang('messages_lang.labelle_cout_livrable') ?> <span style="color: red">*</span></label>
																			<input type="text" name="COUT_LIVRABLE" class="form-control" id="COUT_LIVRABLE">
																		</div>
																	</div>
																</div>

																<div class="row">
																	<div class="col-12">
																		<div class="form-group">
																			<label class="form-label"><?= lang('messages_lang.labelle_beneficiaire') ?> <span style="color: red">*</span></label>
																			<input type="text" name="BENEFICIAIRE_PROJET" class="form-control" id="BENEFICIAIRE_PROJET" max="100" value="<?= isset($oldValues[0]->BENEFICIAIRE_PROJET) ? $oldValues[0]->BENEFICIAIRE_PROJET : '' ?>">
																		</div>
																	</div>
																</div>

																<div class="row">
																	<div class="col-12">
																		<button class="input-group-addon btn btn-secondary mt-5 float-right mb-3" style="display: none;" type="button" id="descr_livrable_save"><?= lang('messages_lang.bouton_ajouter') ?></button>
																	</div>
																</div>
															</div>

															<div class="col-12">
																<table class="table objectif_tab d-none">
																	<thead>
																		<tr>
																			<th><?= lang('messages_lang.labelle_contexte_justification') ?></th>
																			<th><?= lang('messages_lang.labelle_objectif_general') ?></th>
																			<th><?= lang('messages_lang.labelle_objectif_specifique') ?></th>
																			<th><?= lang('messages_lang.labelle_livrable') ?></th>
																			<th></th>
																		</tr>
																	</thead>
																	<tbody>
																	</tbody>
																</table>
															</div>
														</div>

														<div class="row ">
															<div class="col-12">
																<table class="table" id="u_demande_livrable"  style="<?php if(!isset($demande_livrable) || !count($demande_livrable)): ?>display: none;<?php endif; ?>">
																	<thead>
																		<tr>
																			<th>#</th>
																			<th><?= lang('messages_lang.labelle_objectif_specifique') ?></th>
																			<th><?= lang('messages_lang.th_descr_livrable') ?></th>
																			<th><?= lang('messages_lang.labelle_cout_livrable') ?></th>
																			<th></th>
																		</tr>
																	</thead>

																	<tbody>
																		<?php 
																		if(isset($demande_livrable)):
																			$i=1; 
																			foreach($demande_livrable as $demande): ?>
																				<tr>
																					<td><?= $i ?></td>
																					<td><?= $demande->OBJECTIF_SPECIFIQUE ?></td>
																					<td><?= $demande->DESCR_LIVRABLE ?></td>
																					<td><?= number_format($demande->COUT_LIVRABLE,0,',',' ') ?></td>
																					<td class="flex justify-content-end"><div id="supprimer_livrable" data-id="<?= $demande->ID_DEMANDE_LIVRABLE ?>" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
																				</tr>
																				<?php $i++; 
																			endforeach;
																		endif; ?>
																	</tbody>
																</table>
															</div>
														</div>

														<div class="w-100 row justify-content-between">
															<div class="" style="float: left;">
																<button type="button" class="btn btn-info tab_contexte_projet_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
															</div>

															<div class="" style="float: right;">
																<button type="button" <?php if(!isset($oldValues)): ?>disabled<?php endif; ?> class="btn btn-info tab_contexte_projet_next"><?= lang('messages_lang.labelle_etape_suiv') ?></button>
															</div>
														</div>
													</div>
												</div>
											</div>
											<!-- Fin contexte du projet -->

											<!-- Debut impact environnement genre -->
											<div id="tab_impact_environnement_genre" class="container tab-pane">
												<div class="row d-flex align-items-center">
													<div class="py-2 mr-5">
														<div><?= lang('messages_lang.question_impact_env') ?></div>
													</div>
													<div class="">
														<div class="row">
															<div class="form-check form-check-inline">
																<input type="radio" value="1" <?php if(isset($oldValues[0]->A_UNE_IMPACT_ENV) && $oldValues[0]->A_UNE_IMPACT_ENV == '1'): ?> checked <?php endif; ?> NAME="A_UNE_IMPACT_ENV" id="IMPACT_ENV_OUI" class="form-check-input"> <label class="form-check-label" for="IMPACT_ENV_OUI"><?= lang('messages_lang.label_oui') ?></label>
															</div>

															<div class="form-check form-check-inline">
																<input type="radio" value="0" <?php if(isset($oldValues[0]->A_UNE_IMPACT_ENV) && $oldValues[0]->A_UNE_IMPACT_ENV == '0'): ?> checked <?php endif; ?> NAME="A_UNE_IMPACT_ENV" id="IMPACT_ENV_NON" class="form-check-input"> <label class="form-check-label" for="IMPACT_ENV_NON"><?= lang('messages_lang.label_non') ?></label>
															</div>
														</div>
													</div>
												</div>

												<div id="risque_env" <?php if((isset($oldValues[0]->A_UNE_IMPACT_ENV) && $oldValues[0]->A_UNE_IMPACT_ENV == '0') || !isset($oldValues[0]->A_UNE_IMPACT_ENV)): ?> style="display: none;" <?php endif; ?>>
													<div class="row">
														<div class="col-12">
															<div class="form-group">
																<label><?= lang('messages_lang.labelle_impact_env') ?> <font color="red">*</font></label>
																<textarea  autofocus name="IMPACT_ATTENDU_ENVIRONNEMENT" class="form-control" id="IMPACT_ATTENDU_ENVIRONNEMENT" > <?= isset($oldValues[0]->IMPACT_ATTENDU_ENVIRONNEMENT) ? $oldValues[0]->IMPACT_ATTENDU_ENVIRONNEMENT : '' ?></textarea>
															</div>
														</div>
													</div>
												</div>
												<hr>

												<div class="row d-flex align-items-center">
													<div class="py-2 mr-5">
														<div><?= lang('messages_lang.question_impact_genre') ?></div>
													</div>
													<div class="">
														<div class="row">
															<div class="form-check form-check-inline">
																<input type="radio" value="1" <?php if(isset($oldValues[0]->A_UNE_IMPACT_GENRE) && $oldValues[0]->A_UNE_IMPACT_GENRE == '1'): ?> checked <?php endif; ?> NAME="A_UNE_IMPACT_GENRE" id="IMPACT_GENRE_OUI" class="form-check-input"> <label class="form-check-label" for="IMPACT_GENRE_OUI"><?= lang('messages_lang.label_oui') ?></label>
															</div>
															<div class="form-check form-check-inline">
																<input type="radio" value="0" <?php if(isset($oldValues[0]->A_UNE_IMPACT_GENRE) && $oldValues[0]->A_UNE_IMPACT_GENRE == '0'): ?> checked <?php endif; ?> NAME="A_UNE_IMPACT_GENRE" id="IMPACT_GENRE_NON" class="form-check-input"> <label class="form-check-label" for="IMPACT_GENRE_NON"><?= lang('messages_lang.label_non') ?></label>
															</div>
														</div>
													</div>
												</div>

												<div id="risque_genre" <?php if((isset($oldValues[0]->A_UNE_IMPACT_GENRE) && $oldValues[0]->A_UNE_IMPACT_GENRE == '0') || !isset($oldValues[0]->A_UNE_IMPACT_GENRE)): ?> style="display: none;" <?php endif; ?>>
													<div class="row">
														<div class="col-12">
															<div class="form-group">
																<label><?= lang('messages_lang.labelle_impact_genre') ?> <font color="red">*</font></label>
																<textarea name="IMPACT_ATTENDU_GENRE" autofocus class="form-control" id="IMPACT_ATTENDU_GENRE" ><?= isset($oldValues[0]->IMPACT_ATTENDU_GENRE) ? $oldValues[0]->IMPACT_ATTENDU_GENRE : '' ?></textarea>
															</div>
														</div>
													</div>
												</div>

												<div class="w-full w-100 d-flex justify-content-between">
													<div class="" style="float: left;">
														<button type="button" class="btn btn-info tab_impact_environnement_genre_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
													</div>
													<div style="float: right;">
														<button type="button" <?php if(!isset($oldValues) || $oldValues[0]->A_UNE_IMPACT_GENRE == null || $oldValues[0]->A_UNE_IMPACT_ENV == null): ?>disabled<?php endif; ?> class="btn btn-info tab_impact_environnement_genre_next"><?= lang('messages_lang.labelle_etape_suiv') ?></button>
													</div>
												</div>
											</div>
											<!-- Fin impact environnement genre -->

											<!-- Debut risques de projet -->
											<div id="risque_projet_update" class="container tab-pane">
												<div class="row">
													<div class="col-md-12 py-2">
														<div class="row d-flex align-items-center">
															<div class="mr-5"><?= lang('messages_lang.question_risque_projet') ?></div>
															<div class="">
																<div class="row">
																	<div class="form-check form-check-inline">
																		<input type="radio" value="1" <?php if(isset($oldValues[0]->RISQUE_PROJET) && $oldValues[0]->RISQUE_PROJET == '1'): ?> checked <?php endif; ?> NAME="RISQUE_PROJET" id="RISQUE_PROJET_OUI" class="form-check-input"> <label class="form-check-label" for="RISQUE_PROJET_OUI"><?= lang('messages_lang.label_oui') ?></label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input type="radio" value="0" <?php if(isset($oldValues[0]->RISQUE_PROJET) && $oldValues[0]->RISQUE_PROJET == '0'): ?> checked <?php endif; ?> NAME="RISQUE_PROJET" id="RISQUE_PROJET_NON" class="form-check-input"> <label class="form-check-label" for="RISQUE_PROJET_NON"><?= lang('messages_lang.label_non') ?></label>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div id="risque_proj" <?php if((isset($oldValues[0]->RISQUE_PROJET) && $oldValues[0]->RISQUE_PROJET == '0') || !isset($oldValues[0]->RISQUE_PROJET)): ?> class="col-12" style="display: none;" <?php endif; ?>>
														<div class="row">
															<div class="card-body">
																<div class="row">
																	<div class="col-12 col-md-6">
																		<div class="form-group">
																			<label for="RISQUE_PROJET_VALEUR"><?= lang('messages_lang.labelle_risque_projet') ?> <font color="red">*</font></label>
																			<input type="text" autofocus id="RISQUE_PROJET_VALEUR" name="RISQUE_PROJET_VALEUR" value="<?= isset($risquesProjet[0]->NOM_RISQUE) ? $risquesProjet[0]->NOM_RISQUE : '' ?>" class="form-control">
																		</div>
																	</div>

																	<div class="col-12 col-md-6">
																		<div class="form-group">
																			<label for="RISQUE_PROJET_MITIGATION"><?= lang('messages_lang.labelle_mitigation') ?> <font color="red">*</font></label>
																			<input type="text" id="RISQUE_PROJET_MITIGATION" name="RISQUE_PROJET_MITIGATION" value="<?= isset($risquesProjet[0]->MESURE_RISQUE) ? $risquesProjet[0]->MESURE_RISQUE : '' ?>" class="form-control">
																		</div>
																	</div>

																	<div class="col-12 my-4">
																		<button type="button" class="btn btn-primary RISQUE_PROJET_SAVE" style="float: right;"> <?= lang('messages_lang.bouton_ajouter') ?> </button>
																	</div>

																	<div class="col-12 col-md-12">
																		<table class="table RISQUE_PROJET_TABLE <?php if(!isset($risquesProjet) || (isset($risquesProjet) && !$risquesProjet)): ?>d-none<?php endif;?>">
																			<thead>
																				<tr>
																					<th><?= lang('messages_lang.th_risque_associe') ?></th>
																					<th><?= lang('messages_lang.labelle_mitigation') ?></th>
																					<th></th>
																				</tr>
																			</thead>

																			<tbody>
																				<?php if(isset($risquesProjet)): 
																					foreach($risquesProjet as $risque):
																						?>
																						<tr>
																							<td><?= $risque->NOM_RISQUE ?></td>
																							<td><?= $risque->MESURE_RISQUE ?></td>
																							<td class="flex justify-content-end"><div id="supprimerRisqueProjet" data-id="<?= $risque->ID_RISQUE_PROJET ?>" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
																						</tr>
																						<?php 
																					endforeach;
																				endif; ?>
																			</tbody>
																		</table>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div class="w-full w-100 d-flex justify-content-between col-12">
														<div class="" style="float: left;">
															<button type="button" class="btn btn-info RISQUE_PROJET_PREV"><?= lang('messages_lang.labelle_etape_prec') ?></button>
														</div>
														<div style="float: right;">
															<button type="button" <?php if(!isset($oldValues) || $oldValues[0]->RISQUE_PROJET == null): ?>disabled<?php endif; ?> class="btn btn-info RISQUE_PROJET_NEXT"><?= lang('messages_lang.labelle_etape_suiv') ?></button>
														</div>
													</div>
												</div>
											</div>
											<!-- Fin risques de projet -->

											<!-- Debut cadre mesure des résultats -->
											<div id="crm_livrable" class="container tab-pane">
												<div class="row">
													<div class="card-body">
														<div class="row">
															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label for="indicateur_mesure_livrable"><?= lang('messages_lang.labelle_indicateur_mesure') ?> <font color="red">*</font></label>
																	<input type="text" class="form-control " name="indicateur_mesure_livrable" value="" id="indicateur_mesure_livrable">
																</div>
															</div>

															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label for="unite_mesure_livrable"><?= lang('messages_lang.labelle_unite_mesure') ?> <font color="red">*</font></label>
																	<select class="form-control " name="unite_mesure_livrable" id="unite_mesure_livrable">
																		<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<?php 
																		if(isset($unites)): 
																			foreach($unites as $unite):
																				?>
																				<option value="<?= $unite->ID_UNITE_MESURE ?>"><?= $unite->UNITE_MESURE ?></option>
																				<?php 
																			endforeach;
																		endif; ?>
																	</select>
																</div>
															</div>

															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label for=""><?= lang('messages_lang.labelle_cumulative') ?> <span style="color: red;">*</span></label>
																	<select class="form-control" id="cumulative_id" name="cumulative">
																		<option value='' disabled selected>-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<?php foreach($cumulative as $cumile):?>
																			<option value="<?= $cumile->ID_CUMULATIVE ?>"> <?= $cumile->DESCRIPTION_CUMULATIVE ?></option>
																		<?php endforeach ?>
																	</select>
																</div>
															</div>
														</div>
													</div>
												</div>
												<hr style="color: #666;" />

												<h4 class="d-block"><?= lang('messages_lang.labelle_valeur_cible') ?></h4>
												<div class="row mt-3">
													<div id="" class="col-6 col-md-3">
														<div class="form-group">
															<label for="reference_livrable"> <?= lang('messages_lang.labelle_valeur_reference') ?></label>
															<input type="number" min=0 oninput="validity.valid||(value='');"  class="form-control" id="reference_livrable" value="" name="reference_livrable">
														</div>
													</div>

													<div class="col-6 col-md-3">
														<div class="form-group">
															<label for="CRM_livrable_an1"><?= $annees[0]->ANNEE_DESCRIPTION ?> <font color="red">*</font></label>
															<div class="input-group">
																<input type="hidden" id="CRM_livrable_annee_1" name="CRM_livrable_annee_1" value="<?= $annees[0]->ANNEE_BUDGETAIRE_ID ?>">
																<input type="text" name="CRM_livrable_an1" id="CRM_livrable_an1" value="" class="form-control">
															</div>
														</div>
													</div>

													<div class="col-6 col-md-3">
														<div class="form-group">
															<label for="CRM_livrable_an2"><?= $annees[1]->ANNEE_DESCRIPTION ?> <font color="red">*</font></label>
															<div class="input-group">
																<input type="hidden" id="CRM_livrable_annee_2" name="CRM_livrable_annee_2" value="<?= $annees[1]->ANNEE_BUDGETAIRE_ID ?>">
																<input type="text" name="CRM_livrable_an2" id="CRM_livrable_an2" value="" class="form-control">
															</div>
														</div>
													</div>

													<div class="col-6 col-md-3">
														<div class="form-group">
															<label for="CRM_livrable_an3"><?= $annees[2]->ANNEE_DESCRIPTION ?> <font color="red">*</font></label>
															<div class="input-group">
																<input type="hidden" id="CRM_livrable_annee_3" name="CRM_livrable_annee_3" value="<?= $annees[2]->ANNEE_BUDGETAIRE_ID ?>">
																<input type="text" name="CRM_livrable_an3" id="CRM_livrable_an3" value="" class="form-control">
															</div>
														</div>
													</div>
												</div>

												<div class="row">
													<div class="col-12 col-md-3 pt-4" style="float: right;<?php if(isset($oldValues)): ?>display: none;<?php endif; ?>">
														<button class="btn btn-primary cm_livrable_save_button"> <?= lang('messages_lang.bouton_ajouter') ?> </button>
													</div>
												</div>

												<hr style="color: #666;" />
												<div class="col-12">
													<table class="table cadre_mesure_livrable" <?php if(!isset($cmr_livrable) || (isset($cmr_livrable) && !$cmr_livrable)): ?>style="display: none;"<?php endif; ?>>
														<thead>
															<tr>
																<th><?= lang('messages_lang.labelle_livrable') ?></th>
																<th><?= lang('messages_lang.labelle_indicateur_mesure') ?></th>
																<th><?= lang('messages_lang.labelle_cumulative') ?></th>
																<th><?= lang('messages_lang.labelle_unite_mesure') ?></th>
																<th><?= $annees[0]->ANNEE_DESCRIPTION ?></th>
																<th><?= $annees[1]->ANNEE_DESCRIPTION ?></th>
																<th><?= $annees[2]->ANNEE_DESCRIPTION ?></th>
																<th><?= lang('messages_lang.labelle_total') ?></th>
																<th></th>
															</tr>
														</thead>

														<tbody>
															<?php if(isset($cmr_livrable)): 
																foreach($cmr_livrable as $key => $cadre):    
																	?>
																	<tr>
																		<td><?= $cadre->DESCR_LIVRABLE ?></td>
																		<td><?= $cadre->INDICATEUR_MESURE ?></td>
																		<td><?= $cadre->DESCRIPTION_CUMULATIVE ?></td>
																		<td><?= $cadre->UNITE_MESURE ?></td>
																		<?php foreach($cmr_cible[$key] as $cible): ?>
																			<td><?= number_format($cible->VALEUR_ANNEE_CIBLE,0,'.',' ') ?></td>
																		<?php endforeach; ?>
																		<td><?= number_format($cadre->TOTAL_TRIENNAL,0,'.',' ') ?></td>
																		<td class="flex justify-content-end"><div id="supprimer_crm_livrable" data-id="<?= $cadre->ID_CADRE_MESURE_RESULTAT_LIVRABLE ?>" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
																	</tr>
																	<?php
																endforeach; 
															endif; ?>
														</tbody>
													</table>
												</div>

												<div class="col-md-12">
													<button type="button" id="btn_piece" class="btn btn-info crm_livrable_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
													<button type="button" id="btn_piece" class="btn btn-info crm_livrable_next" style="float: right" <?php if(!isset($cmr_livrable) || !count($cmr_livrable)): ?>disabled<?php endif; ?>><?= lang('messages_lang.labelle_etape_suiv') ?></button>
												</div>
											</div>
											<!-- Fin cadre mesure des résultats -->

											<!-- Debut budget du projet par livrable -->
											<div id="bpl" class="container tab-pane">
												<div class="row">
													<div class="card-body">
														<div class="row">
															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label for="BPL_livrable"><?= lang('messages_lang.labelle_livrable') ?> <font color="red">*</font></label>
																	<select class="form-control " name="BPL_livrable" id="BPL_livrable" onchange="get_tous_info_livrable()" >
																		<option selected value="">-- <?= lang('messages_lang.selection_message') ?> --</option>
																	</select>
																</div>
															</div>

															<div class="col-12 col-md-6">
																<div class="form-group">
																	<label for="cout_unitaire"><?= lang('messages_lang.labelle_cout_livrable') ?> <font color="red">*</font></label>
																	<input type="number" min=0 oninput="validity.valid||(value='');"  class="form-control " readonly name="cout_unitaire" value="" id="cout_unitaire">
																</div>
															</div>

															<!-- tableau -->
															<div class="col-md-12">
																<table id="mytable12" class="table table-bordered">
																	<thead>
																		<th><?= lang('messages_lang.th_nomenclature') ?></th>
																		<th>%</th>
																		<th><?= $annees[0]->ANNEE_DESCRIPTION ?><br><div id="cible1"></div></th>
																		<th><?= $annees[1]->ANNEE_DESCRIPTION ?><br><div id="cible2"></div></th>
																		<th><?= $annees[2]->ANNEE_DESCRIPTION ?><br><div id="cible3"></div></th>
																	</thead>
																	<tbody>
																	</tbody>
																</table>
															</div>
															<!-- end table -->

															<div class="w-full w-100 d-flex justify-content-between">
																<div class="" style="float: left;">
																	<button type="button" id="btn_piece" class="btn btn-info bpl_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
																</div>

																<div class="" style="float: right;">
																	<button type="button" id="btn_piece" class="btn btn-info bpl_next" disabled><?= lang('messages_lang.labelle_etape_suiv') ?></button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<!-- Fin budget du projet par livrable -->

											<!-- Debut source de financement -->
											<div id="sfp" class="container tab-pane">
												<?php
													$source_financement = "";
													$monnaie = "";
													$financement = "";
													$bif = "";
													$annee = [];

													if(isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '0' && count($sfp)){
														$source_financement = $sfp[0]->ID_SOURCE_FINANCE_BAILLEUR;
														$monnaie = $sfp[0]->TAUX_ECHANGE_ID;
														$financement = number_format($sfp[0]->TOTAL_TRIENNAL,0,'.',' ');
														$bif = number_format($sfp[0]->TOTAL_FINANCEMENT * $sfp[0]->TAUX,0,'.',' ');

														foreach($cibles[0] as $scm){
															$annee[] = number_format($scm->SOURCE_FINANCEMENT_VALEUR_CIBLE,0,'.',' ');
														}
													}
												?>
												<div class="row">
													<div class="card-body">
														<div class="col-12">
															<div class="form-group">
																<div class="row d-flex align-items-center">
																	<div class="pb-3 mt-4 mr-5">
																		<p><?= lang('messages_lang.question_source_finance') ?></p>
																	</div>

																	<div class="row">
																		<div class="form-check form-check-inline">
																			<input type="radio" name="EST_CO_FINANCE" value="1" <?php if(isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '1'): ?>checked<?php endif; ?> class="form-check-input" id="EST_CO_FINANCE_OUI">
																			<label class="form-check-label" for="EST_CO_FINANCE_OUI"><?= lang('messages_lang.label_oui') ?></label>
																		</div>

																		<div class="form-check form-check-inline">
																			<input type="radio" name="EST_CO_FINANCE" value="0" <?php if(isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '0'): ?>checked<?php endif; ?> class="form-check-input" id="EST_CO_FINANCE_NON">
																			<label class="form-check-label" for="EST_CO_FINANCE_NON"><?= lang('messages_lang.label_non') ?></label>
																		</div>
																	</div>
																</div>
															</div>
														</div>

														<div class="row" id="financement" <?php if(!isset($oldValues[0]->EST_CO_FINANCE)): ?>style="display: none;"<?php endif; ?>>
															<input type="hidden" name="USD_value" id="USD_value" value="<?= isset($usd) && !empty($usd) ? $usd[0]->TAUX : 1 ?>">
															<input type="hidden" name="EURO_value" id="EURO_value" value="<?= isset($euro) && !empty($euro) ? $euro[0]->TAUX : 1 ?>">
															<div class="col-12 col-md-3">
																<div class="form-group">
																	<label for="bailleur"><?= lang('messages_lang.labelle_sfp') ?> <font color="red">*</font></label>
																	<select class="form-control select2" autofocus name="bailleur" id="bailleur">
																		<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<?php 
																		if(isset($bailleurs)): 
																			foreach($bailleurs as $bailleur):
																				?>
																				<option value="<?= $bailleur->ID_SOURCE_FINANCE_BAILLEUR ?>" <?php if($bailleur->ID_SOURCE_FINANCE_BAILLEUR == $source_financement): ?> selected <?php endif; ?>><?= $bailleur->CODE_BAILLEUR.' - '.$bailleur->NOM_SOURCE_FINANCE ?></option>
																				<?php 
																			endforeach;
																		endif; ?>
																	</select>
																</div>
															</div>

															<div class="col-12 col-md-3">
																<div class="form-group">
																	<label for="devise_financement"><?= lang('messages_lang.labelle_monnaie') ?> <font color="red">*</font></label>
																	<select class="form-control select2" name="devise_financement" id="devise_financement">
																		<option value="" selected disabled>-- <?= lang('messages_lang.selection_message') ?> --</option>
																		<?php foreach($devises as $devise): ?>
																			<option value="<?= $devise->TAUX_ECHANGE_ID ?>" <?php if($devise->TAUX_ECHANGE_ID == $monnaie): ?>selected<?php endif ?> data-taux="<?= $devise->TAUX ?>"><?= $devise->DEVISE ?></option>
																		<?php endforeach; ?>
																	</select>
																</div>
															</div>

															<div class="col-12 col-md-3">
																<div class="form-group">
																	<label for="total_financement"><?= lang('messages_lang.labelle_financement') ?> <font color="red">*</font></label>
																	<input type="text" name="total_financement" value="<?= $financement ?>" id="total_financement" class="form-control">
																</div>
															</div>

															<div class="col-12 col-md-3">
																<div class="form-group">
																	<label for="total_financement_bif"><?= lang('messages_lang.labelle_montant_BIF') ?></label>
																	<input type="text" name="total_financement_bif" value="<?= $bif ?>" readonly id="total_financement_bif" class="form-control">
																</div>
															</div>

															<div>
																<hr style="color: #666;" />
																<h3><?= lang('messages_lang.labelle_valeur_nominale') ?></h3>
																<div class="row">
																	<div class="col-12 col-md-4">
																		<div class="form-group">
																			<label for="SFP_an1"><?= $annees[0]->ANNEE_DESCRIPTION ?> <font color="red">*</font></label>
																			<div class="input-group">
																				<input type="hidden" id="annee_sfp_1" name="annee_sfp_1" value="<?= $annees[0]->ANNEE_BUDGETAIRE_ID ?>">
																				<input type="text" name="SFP_an1" id="SFP_an1" value="<?= $annee[0] ?? '' ?>" class="form-control">
																			</div>
																		</div>
																	</div>

																	<div class="col-12 col-md-4">
																		<div class="form-group">
																			<label for="SFP_an2"><?= $annees[1]->ANNEE_DESCRIPTION ?> <font color="red">*</font></label>
																			<div class="input-group">
																				<input type="hidden" id="annee_sfp_2" name="annee_sfp_2" value="<?= $annees[1]->ANNEE_BUDGETAIRE_ID ?>">
																				<input type="text" name="SFP_an2" id="SFP_an2" value="<?= $annee[1] ?? '' ?>" class="form-control">
																			</div>
																		</div>
																	</div>

																	<div class="col-12 col-md-4">
																		<div class="form-group">
																			<label for="SFP_an3"><?= $annees[2]->ANNEE_DESCRIPTION ?> <font color="red">*</font></label>
																			<div class="input-group">
																				<input type="hidden" id="annee_sfp_3" name="annee_sfp_3" value="<?= $annees[2]->ANNEE_BUDGETAIRE_ID ?>">
																				<input type="text" name="SFP_an3" id="SFP_an3" value="<?= $annee[2] ?? '' ?>" class="form-control">
																			</div>
																		</div>
																	</div>
																</div>
															</div>

															<div class="col-12 my-4 d-flex justify-content-end">
																<button class="btn btn-primary sfp_save_button mb-4" style="<?php if(isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '0'): ?>display: none;<?php endif; ?>"> <?= lang('messages_lang.bouton_ajouter') ?> </button>
															</div>
														</div>
													</div>
												</div>

												<div class="col-12 col-md-12">
													<table class="table sfp_tab <?php if(!isset($sfp) || (isset($sfp) && count($sfp) == 0) || (isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '0')): ?>d-none<?php endif; ?>">
														<thead>
															<tr>
																<th><?= lang('messages_lang.labelle_sfp') ?></th>
																<th><?= $annees[0]->ANNEE_DESCRIPTION ?></th>
																<th><?= $annees[1]->ANNEE_DESCRIPTION ?></th>
																<th><?= $annees[2]->ANNEE_DESCRIPTION ?></th>
																<th><?= lang('messages_lang.labelle_monnaie') ?></th>
																<th><?= lang('messages_lang.labelle_financement') ?></th>
																<th><?= lang('messages_lang.labelle_montant_BIF') ?></th>
																<th></th>
															</tr>
														</thead>

														<tbody>
															<?php if(isset($sfp) && isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '1' || (isset($oldValues[0]->EST_CO_FINANCE) && empty($oldValues[0]->EST_CO_FINANCE))): 
																foreach($sfp as $source):
																	?>
																	<tr>
																		<td><?= $source->NOM_SOURCE_FINANCE ?></td>
																		<?php foreach($cibles[0] as $scm): ?>
																			<td><?= number_format($scm->SOURCE_FINANCEMENT_VALEUR_CIBLE,0,'.',' ') ?></td>
																		<?php endforeach; ?>
																		<td><?= $source->DEVISE ?></td>
																		<td><?= number_format($source->TOTAL_TRIENNAL,0,'.',' ') ?></td>
																		<td><?= number_format($source->TOTAL_FINANCEMENT * $source->TAUX,0,'.',' ') ?></td>
																		<td class="flex justify-content-end"><div id="supprimer_sfp" data-id="<?= $source->ID_DEMANDE_SOURCE_FINANCEMENT ?>" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
																	</tr>
																	<?php 
																endforeach;
															endif; ?>
														</tbody>
													</table>
												</div>

												<div class="w-full w-100 d-flex justify-content-between">
													<div class="" style="float: left;">
														<button type="button" class="btn btn-info float-right sfp_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
													</div>
													<div style="float: right;">
														<button type="button" <?php if((!isset($sfp) && isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == '1' || (isset($oldValues[0]->EST_CO_FINANCE) && $oldValues[0]->EST_CO_FINANCE == null)) || (isset($sfp) && count($sfp) == 0 )): ?>disabled<?php endif;?> class="btn btn-info save_all"><?= lang('messages_lang.labelle_etape_suiv') ?></button>
													</div>
												</div>
											</div>
											<!-- Fin source de financement -->

											<!-- Debut Observation Complémentaire -->
											<div id="observation_complementaire" class="container tab-pane">
												<div class="row">
													<div class="card-body">
														<div class="row">
															<div class="col-12">
																<div class="form-group">
																	<label><?= lang('messages_lang.tab_observation_complementaire') ?></label>
																	<textarea type="text" autofocus name="OBSERVATION_COMPLEMENTAIRE" rows="10" class="form-control" id="OBSERVATION_COMPLEMENTAIRE"> <?= isset($oldValues[0]->OBSERVATION_COMPLEMENTAIRE) ? $oldValues[0]->OBSERVATION_COMPLEMENTAIRE : '' ?></textarea>
																</div>
															</div>
														</div>

														<div class="row">
															<div class="w-full w-100 d-flex justify-content-between">
																<div class="" style="float: left;">
																	<button type="button" class="btn btn-info float-right observation_prev"><?= lang('messages_lang.labelle_etape_prec') ?></button>
																</div>
																<div style="float: right;">
																	<button type="submit" class="btn btn-info"><?= lang('messages_lang.bouton_enregistrer') ?></button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<!-- Fin Observation Complémentaire -->
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
	<div id="modal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<embed id="pdf2" style="display:none;" src="" type="application/pdf" width="100%" height="600px"></embed>
				</div>
			</div>
		</div>
	</div>
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
</body>
</html>

<?php echo view('includesbackend/scripts_js.php'); ?>
<!-- /** ================================================================================== */ -->
<script>
	$(document).ready(function()
	{
		$('#projet').addClass('container tab-pane active');
		$('#tab1').addClass('active');
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#crm').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();
		$('#govzone').prop('hidden', true);
		$('#organisateur').prop('hidden', true);
		$('#organisation_id').prop('hidden', true);
		$('#source_financment').prop('hidden', true);
			//cacher les information sur etude documents
		$('#doc_reference').hide()
	})

	/* completer les options dans select */
	$.ajax(
	{
		type: "GET",
		url: "/pip/crm/",
		success: function(response)
		{
			<?php if(!isset($infos)): ?>
				response.demande.forEach(element =>
				{
					if (element.ID_DEMANDE_INFO_SUPP && element.NOM_PROJET)
					{
						$('#projet_treso').append(`
							<option value="${element.ID_DEMANDE_INFO_SUPP}">${element.NOM_PROJET}</option>
							`)
					}
				})
			<?php endif; ?>

			<?php if(!isset($categories)): ?>
				response.category.forEach(element =>
				{
					if (element.ID_CATEGORIE_LIBELLE && element.CATEGORIE_LIBELLE)
					{
						$('#libelle').append(`
							<option value="${element.ID_CATEGORIE_LIBELLE}">${element.CATEGORIE_LIBELLE}</option>
							`)
					}
				})
			<?php endif; ?>

			<?php if(!isset($unites)): ?>
				response.unite.forEach(element =>
				{
					if (element.ID_UNITE_MESURE && element.UNITE_MESURE)
					{
						$('#unite_mesure_objectif_general').append(`
							<option value="${element.ID_UNITE_MESURE}">${element.UNITE_MESURE}</option>                
							`)

						$('#unite_mesure_objectif_specifique').append(`
							<option value="${element.ID_UNITE_MESURE}">${element.UNITE_MESURE}</option>                
							`)

						$('#unite_mesure_livrable').append(`
							<option value="${element.ID_UNITE_MESURE}">${element.UNITE_MESURE}</option>                
							`)
					}
				})
			<?php endif; ?>

			<?php if(!isset($indicateurs)): ?>
				response.indicateur.forEach(element =>
				{
					if (element.ID_INDICATEUR_MESURE && element.INDICATEUR_MESURE)
					{
						$('#indicateur_mesure_objectif_general').append(`
							<option value="${element.ID_INDICATEUR_MESURE}">${element.INDICATEUR_MESURE}</option>                
							`)

						$('#indicateur_mesure_objectif_specifique').append(`
							<option value="${element.ID_INDICATEUR_MESURE}">${element.INDICATEUR_MESURE}</option>                
							`)

						$('#indicateur_mesure_livrable').append(`
							<option value="${element.ID_INDICATEUR_MESURE}">${element.INDICATEUR_MESURE}</option>                
							`)
					}
				})
			<?php endif; ?>

			<?php if(!isset($bailleurs)): ?>
				response.source_financement.forEach(element =>
				{
					if (element.ID_SOURCE_FINANCE_BAILLEUR && element.NOM_SOURCE_FINANCE)
					{
						$('#bailleur').append(`
							<option value="${element.ID_SOURCE_FINANCE_BAILLEUR}">${element.CODE_BAILLEUR} - ${element.NOM_SOURCE_FINANCE}</option>

							`)
					}
				})
			<?php endif; ?>
		},
		error: function(error)
		{
			console.log(error)
		}
	});

	function get_tous_info_livrable() 
	{
		$('.bpl_next').removeAttr('disabled')
		var id= $('#BPL_livrable').val()
		var row_count = "1000000";
		$("#mytable12").DataTable(
		{
			"destroy": true,
			"processing": true,
			"serverSide": true,
			"ajax":
			{
				url: "<?= base_url()?>/pip/Processus_Investissement_Public_Demande/get_info_livrable_cmr/"+id,
				type: "POST",
			},
			success: function(response)
			{
				$("#cible1").html(cible1)
				$("#cible2").html(cible2)
				$("#cible3").html(cible3)
			},
			lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
			pageLength: 10,
			"columnDefs": [{
				"targets": [],
				"orderable": false
			}],

			dom: 'Bfrtlip',
			order: [],
			buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
			language:
			{
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

	function deleteInfo(table,element,demande)
	{
		$.ajax({
			url: '/pip/Processus_Investissement_Public/demande/deleteInfo',
			type: "POST",
			data: {
				table: table,
				id: demande,
			},
			error: function(error){
				console.error(error)
			}
		})

		if(!$(element).hasClass('modified')){
			$(element).children('tbody').empty()
			$(element).addClass('modified')
		}
	}

	const btnSubmitForm = document.querySelector(".js-formSubmit")
	
	// soumettre le formulaire
	btnSubmitForm.addEventListener("submit", function(e)
	{
		e.preventDefault()
		const target = e.currentTarget

		const data = new FormData(target)

		<?php if(isset($oldValues)): ?>
			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/updateInfo',
				data: data,
				success: function(response)
				{
					window.location.href = "/pip/Projet_Pip_Fini/liste_pip_fini"
				},
				error: function(error)
				{
					console.error(error)
				},
				contentType: false,
				cache: false,
				processData: false,
			})
		<?php else: ?>
			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/store',
				data: data,
				success: function(response)
				{
					window.location.href = "/pip/Projet_Pip_Fini/liste_pip_fini"
				},
				error: function(error)
				{
					console.error(error)
				},
				contentType: false,
				cache: false,
				processData: false,
			})
		<?php endif; ?>
	})

	document.querySelectorAll(".radio-select").forEach(el =>
	{
		el.addEventListener("click", e =>
		{
			const target = e.currentTarget
			const parenEl = target.parentNode.parentNode.parentNode.parentNode
			if (target.value == 0)
			{
				parenEl.querySelector(".cartographie").classList.remove("d-none")
			}
			else if(target.value == 1)
			{
				parenEl.querySelector(".cartographie").classList.add("d-none")
			}
		})
	})

	let tabs = ['#tab1','#tab10','#tab2','#tab3','#tab4','#tab5','#tab6','#tab12','#tab13','#tab7','#tab8','#tab11','#tab14']

	let provinceInfoSup = []
	let tab2infoSup = []
	let cmrTab5infoSup = []
	let bplTab7infoSup = []
	let Tab9infoSup = []

	document.querySelectorAll("[data-toggle='tab']").forEach((element) => 
	{
		element.addEventListener('click', (ev) => {
			return false
		});
	});

	function removeFeedBack()
	{
		$('div .invalid-feedback').remove()
		$('div .is-invalid').removeClass('is-invalid')
	}

	function createElementError(element, message = null)
	{
		const el = document.createElement("div")
		element.classList.add('is-invalid')
		if(message !== null)
		{
			el.innerText = message
		}
		else
		{
			el.innerText = "<?= lang('messages_lang.message_champs_obligatoire') ?>"
		}
		el.classList.add("invalid-feedback")
		element.parentNode.appendChild(el)
	}
</script>

<!-- Debut description (info principal du projet) -->
<script>
	$('#btn_projet_save').click(function()
	{
		removeFeedBack()
		let projetFillable = ['ID_STATUT_PROJET','NOM_PROJET','DATE_DEBUT_PROJET','DATE_FIN_PROJET','DUREE_PROJET','ID_AXE_INTERVENTION_PND','INSTITUTION_ID','ID_PILIER','ID_OBJECT_STRATEGIQUE','ID_OBJECT_STRATEGIC_PND','ID_PROGRAMME_PND','ID_PROGRAMME','ID_ACTION'];
		let success = true
		projetFillable.forEach(element =>
		{
			if($('#'+element).val() == '' || $('#'+element).val() == null){
				createElementError(document.querySelector('#'+element))
				success = false
			}
		})

		let minDebut = $('#DATE_DEBUT_PROJET').attr('min')
		let minFin = $('#DATE_FIN_PROJET').attr('min')

		let debut = $('#DATE_DEBUT_PROJET').val()
		let fin = $('#DATE_FIN_PROJET').val()

		if(new Date(debut) > new Date(fin))
		{
			$('#DATE_DEBUT_PROJET').addClass('is-invalid')
			$('#DATE_FIN_PROJET').addClass('is-invalid')

			$('#DATE_DEBUT_PROJET').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_date_inf') ?></div>')
			success = false
		}

		if(new Date(minDebut) > new Date(debut))
		{
			$('#DATE_DEBUT_PROJET').addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_date_limit') ?> '+minDebut+'</div>')

			success = false
		}

		if(new Date(minFin) > new Date(fin))
		{
			$('#DATE_FIN_PROJET').addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_date_fin_limit') ?> '+minFin+'</div>')

			success = false
		}

		if($('#DUREE_PROJET').val() < 12)
		{
			$('#DUREE_PROJET').addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_duree_min') ?></div>')

			success = false
		}

		if(success)
		{
			$(this).attr('disabled','disabled')
			const formData = new FormData()
			projetFillable.forEach(element => {
				formData.append(element, $('#'+element).val())
			})

			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/storeProjet',
				data: formData,
				dataType: 'JSON',
				beforeSend: function()
        {
          $('#loading_sv_projet').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#btn_projet_save').attr('disabled',true);
        },
				success: function(response)
				{
					$('#loading_sv_projet').html(" ");
					$('.js-formSubmit').append('<input type="hidden" name="demande_id" id="demande_id" value="'+response+'">')
					$('#js-validate-data').removeAttr('disabled').show()
					$('#btn_projet_save').hide()
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	// afficher le button update on change
	let projetFillable = ['ID_STATUT_PROJET','NOM_PROJET','DATE_DEBUT_PROJET','DATE_FIN_PROJET','DUREE_PROJET','ID_AXE_INTERVENTION_PND','INSTITUTION_ID','ID_PILIER','ID_OBJECT_STRATEGIQUE','ID_OBJECT_STRATEGIC_PND','ID_PROGRAMME_PND','ID_PROGRAMME','ID_ACTION'];

	projetFillable.forEach(element => 
	{
		$('#'+element).change(function()
		{
			$('#btn_projet_update').show().removeAttr('disabled')

			if(!$('#btn_projet_update').length && $('#btn_projet_save').is(':hidden'))
			{
				$('#btn_projet').parent().prev().children().html(`
					<button type="button" id="btn_projet_update" class="btn btn-primary float-right mb-3"><?= lang('messages_lang.labelle_mettre_a_jour') ?></button>
					`)

				$('#btn_projet_update').click(projetUpdate)
			}

			$('#js-validate-data').attr('disabled','disabled')
		})
	});

	// Mettre à jour la première étape
	function projetUpdate()
	{
		removeFeedBack()
		let success = true

		projetFillable.forEach(element =>
		{
			if($('#'+element).val() == '')
			{
				createElementError(document.querySelector('#'+element))
				success = false
			}
		})

		let minDebut = $('#DATE_DEBUT_PROJET').attr('min')
		let minFin = $('#DATE_FIN_PROJET').attr('min')

		let debut = $('#DATE_DEBUT_PROJET').val()
		let fin = $('#DATE_FIN_PROJET').val()

		if(new Date(debut) > new Date(fin))
		{
			$('#DATE_DEBUT_PROJET').addClass('is-invalid')
			$('#DATE_FIN_PROJET').addClass('is-invalid')

			$('#DATE_DEBUT_PROJET').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_date_inf') ?></div>')
			success = false
		}

		if(new Date(minDebut) > new Date(debut))
		{
			$('#DATE_DEBUT_PROJET').addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_date_limit') ?> '+minDebut+'</div>')

			success = false
		}

		if(new Date(minFin) > new Date(fin))
		{
			$('#DATE_FIN_PROJET').addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_date_fin_limit') ?> '+minFin+'</div>')

			success = false
		}

		if($('#DUREE_PROJET').val() < 12)
		{
			$('#DUREE_PROJET').addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_erreur_duree_min') ?></div>')

			success = false
		}

		if(success)
		{
			$('#btn_projet_update').attr('disabled','disabled')
			const formData = new FormData()

			projetFillable.forEach(element =>
			{
				formData.append(element, $('#'+element).val())
			})

			formData.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())

			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/updateProjet',
				data: formData,
				beforeSend: function()
        {
          $('#loading_updt_projet').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#btn_projet_update').attr('disabled',true);
        },
				success: function(response)
				{
					$('#js-validate-data').removeAttr('disabled').show()
					$('#btn_projet_update').hide()
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	}

	$('#btn_projet_update').click(projetUpdate)

	// Choisir le programme lié à l'institution
	$('#INSTITUTION_ID').change(function()
	{
		let id = $(this).val()

		$.ajax({
			type: 'GET',
			url: '/pip/Processus_Investissement_Public/demande/filtre/programme/'+id,
			dataType: "JSON",
			success: function(response){
				$('.js-id-programe').html(response)
			},
			error: function(error){
				console.error(error)
			}
		})
	})

	// Afficher les programmes
	$(".js-id-programe").change(function()
	{
		let idPrograme = $(this).val()

		$.ajax({
			type: 'GET',
			url: '/pip/Processus_Investissement_Public/demande/filter/'+idPrograme,
			dataType: "JSON",
			success: function(response)
			{
				$('.actions-elements').html(response)
			},
			error: function(error)
			{
				console.error(error)
			}
		})
	})

	// Calcul de la durée du projet
	$('#DATE_DEBUT_PROJET').change(function()
	{
		let debut = $(this).val()
		let fin = $('#DATE_FIN_PROJET').val()

		if(debut != '' && fin != '')
		{
			let duree = monthDiff(new Date(debut),new Date(fin))
			$('#DUREE_PROJET').val(duree)
		}
	})

	$('#DATE_FIN_PROJET').change(function()
	{
		let debut = $(this).val()
		let fin = $('#DATE_DEBUT_PROJET').val()

		if(debut != '' && fin != ''){
			let duree = monthDiff(debut,fin)
			$('#DUREE_PROJET').val(duree)
		}
	})

	function monthDiff(d1, d2) 
	{
		d1 = new Date(d1)
		d2 = new Date(d2)

		var months;
		months = (d2.getFullYear() - d1.getFullYear()) * 12;
		months -= d1.getMonth();
		months += d2.getMonth();
			// console.log(months);
		return Math.abs(months);
	}
	// ==== Fin calcul de la durée du projet
</script>
<!-- Fin description (info principal du projet) -->

<!--Debut Lieu d'intervention -->
<script>
	// Avancer l'étape de lieu d'intervention à etude et documents
	$('#lieu_intervention_next').click(function()
	{
		let lieuChecked = 0
		let radioSelect = $('div .radio-select')

		for(let i=0; i < radioSelect.length; i++)
		{
			if(radioSelect[i].checked)
			{
				lieuChecked++
			}
		}

		if(lieuChecked)
		{
			// Changer de tab
			let intervention = new FormData()

			if($('#inlineRadio2').is(':checked'))
			{
				intervention.append('EST_REALISE_NATIONAL', 0)
			}
			else
			{
				intervention.append('EST_REALISE_NATIONAL', 1)
			}

			intervention.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())

			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/store/step/intervention',
				data: intervention,
				success: function(response)
				{
					tabs.forEach(el =>
					{
						$(el).removeClass('active')
					})

					$('#tab2').addClass('active')

					$('#lieu_intervention').hide()
					$('#projet').hide()
					$('#tab_etude_document').show()

					removeFeedBack()

					if($('#inlineRadio1').is(':checked'))
					{
						$('.tab_projet tbody').children().remove()
						$('.tab_projet').addClass('d-none')
					}
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	// Revenir à l'étape précedente
	$('#lieu_intervention_prev').click(function()
	{
		// Revenir au premier tab
		$('#tab10').removeClass('active')
		$('#tab2').removeClass('active')
		$('#tab1').addClass('active')

		$('#lieu_intervention').hide()
		$('#tab_etude_document').hide()
		$('#projet').show()
	})

	// choisir si le projet se réalise au niveau national
	$('#inlineRadio2').change(function()
	{
		if($(this).is(':checked'))
		{
			$('.projet_save_button').parent().show()
			$('#lieu_intervention_next').attr('disabled','disabled')
		}
		else
		{
			$('.projet_save_button').parent().hide()
			$('#lieu_intervention_next').removeAttr('disabled')
		}
	})

	$('#inlineRadio1').change(function()
	{
		if($(this).is(':checked'))
		{
			$('.projet_save_button').parent().hide()
			$('#lieu_intervention_next').removeAttr('disabled')
		}
		else
		{
			$('.projet_save_button').parent().show()
			$('#lieu_intervention_next').attr('disabled','disabled')
		}
	})

	// Choisir la commune lié à au province
	$('#ID_PROVINCE').change(function()
	{
		let id = $(this).val()
		$('.projet_save_button').parent().show()
		if(id!="")
		{
			$.ajax(
			{
				type: 'GET',
				url: '/pip/Processus_Investissement_Public/demande/filtre/commune/'+id,
				dataType: "JSON",
				success: function(response)
				{
					$('#ID_COMMUNE').html(response)
				},
				error: function(error)
				{
					console.error(error)
				}
			})
		}
	})

	//active le bouton ajouter
	$('#ID_COMMUNE').change(function ()
	{
		$('.projet_save_button').parent().show()    
	})

	function modal_comm(id,demande)
	{
		$.ajax({
			url:"<?=base_url()?>/pip/Processus_Investissement_Public/getcommunes/"+id+"/"+demande,
			type:"GET",
			dataType:"JSON",
			success: function(data)
			{
				$('#communes').html(data.html);
				$('#province').html(data.PROVINCE);
				$('#detail_comm').modal('show');
			}
		});
		
		return false
	}

	// Ajouter au tableau province et commune
	const addProjectToTable = (province,provinceId, commune,lieu,demande) =>
	{
		const body = $(".tab_projet")
		const tbody = $('.tab_projet tbody')

		if(tbody.length)
		{
			tbody.append(`<tr>
				<td>${province}</td>
				<td><a class="btn btn-primary" onclick="modal_comm(${provinceId},${demande})">${commune}</a></td>

				</tr>`)
		}
		else
		{
			body.append(`
				<tbody>
				<tr>
				<td>${province}</td>
				<td><a class="btn btn-primary" onclick="modal_comm(${provinceId},${demande})">${commune}</a></td>
				</tr>
				</tbody>
				`)
		}
	}

	// Supprimer dans la table lieu intervention
	function supprimerLieu(id,province,element)
	{
		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/lieu/cible',
			type: "POST",
			data:
			{
				id: id,
			},
			success: function(response)
			{
				$("#"+element).remove()

				let tab_projet = $('.tab_projet tbody').children()
				for(let i=0;i < tab_projet.length;i++)
				{
					let tds = tab_projet[i].childNodes
					let communeCount = 0
					let pr = tds[1].innerText
					pr = pr.trim()
					province = province.trim()
					if(pr == province)
					{
						communeCount = parseInt(tds[3].innerText)
						tds[3].childNodes[0].innerText = communeCount - 1
					}

					if((communeCount - 1) == 0)
					{
						document.querySelector('.tab_projet tbody').removeChild(tab_projet[i])
						$('#detail_comm').modal('hide');
					}
				}

				if($('.tab_projet tbody').children().length == 0)
				{
					$('#lieu_intervention_next').attr('disabled','disabled')
					$('.tab_projet').addClass('d-none')
				}
			},
			error: function(error)
			{
				console.error(error)
			}
		});

		return false
	}

	const fillableProject = () => 
	{
		return [
			"[name='DATE_DEBUT_PROJET']","[name='DUREE_PROJET']", "[name='ID_ACTION']", "[name='ID_PROGRAMME_PND']","[name='ID_PROGRAMME']", "[name='ID_AXE_INTERVENTION_PND']",
			"[name='ID_OBJECT_STRATEGIC_PND']", "[name='ID_OBJECT_STRATEGIQUE']", "[name='INSTITUTION_ID']", "[name='ID_PILIER']",
			"[name='DATE_FIN_PROJET']", "[name='NOM_PROJET']", "[name='ID_STATUT_PROJET']",
			]
	}

	// Ajouter dans la table lieu intervention
	document.querySelector(".projet_save_button").addEventListener("click", (ev) =>
	{
		$('.projet_save_button').attr('disabled','disabled')
		ev.preventDefault()
		removeFeedBack()
		const province = document.querySelector("#ID_PROVINCE").selectedIndex
		var commune = $('#ID_COMMUNE').val()
		const provinceText = document.querySelector("#ID_PROVINCE").options[province].text
		var communeText = $('#ID_COMMUNE option:selected').toArray().map(item => item.text).join();
		let success = true
		if (success)
		{
			if(!document.querySelector("[name='EST_REALISE_NATIONAL']").checked)
			{
				let id = $('#demande_id').val()

				let tab_projet = $('.tab_projet tbody').children()
				let successProject = true
				let ID_PROVINCE = document.getElementById("ID_PROVINCE").value

				const fillableProject = ['#ID_PROVINCE', '#ID_COMMUNE']

				fillableProject.forEach((el) =>
				{
					if (document.querySelector(el).value === "")
					{
						createElementError(document.querySelector(el))
						successProject = false
					}
				})

				if(tab_projet.length)
				{
					const verifierLieu = new FormData()
					verifierLieu.append('province',ID_PROVINCE)
					verifierLieu.append('commune',commune)
					verifierLieu.append('ID_DEMANDE_INFO_SUPP',id)
 
					$.ajax(
					{
						type: 'POST',
						url: '/pip/Processus_Investissement_Public/demande/lieuIntervention',
						data: verifierLieu,
						success: function(response)
						{
							if(!Array.isArray(response))
							{
								enregistrer_lieu(successProject,tab_projet,id,ID_PROVINCE)
							}
							else
							{

								$('.cartographie').before('<div class="alert alert-danger mt-2">La province et la commune ont déjà été ajoutées</div>')
								$('.alert.alert-danger').delay(2000).fadeOut('slow');
								$('.projet_save_button').removeAttr('disabled');
							}
							
						},
						error: function(error)
						{
							console.error(error)
						},
						cache: false,
						contentType: false,
						processData: false
					})
				}
				else
				{
					enregistrer_lieu(successProject,tab_projet,id,ID_PROVINCE)
				}

				function enregistrer_lieu(successProject,tab_projet,id,ID_PROVINCE)
				{
					if (successProject)
					{
						let communeCount = 0
						let communeCountSuccess = true

						if(tab_projet.length)
						{
							for(let i=0;i < tab_projet.length;i++)
							{
								let tds = tab_projet[i].childNodes

								if(tds[1].innerText == provinceText)
								{
									communeCount = parseInt(tds[3].innerText)
									communeCountSuccess = false
									tds[3].childNodes[0].innerText = communeCount + commune.length
								}
							}
						}

						communeCount += commune.length

						const IntervationFormData = new FormData()
						if($('#demande_id').length)
						{
							IntervationFormData.append('ID_DEMANDE_INFO_SUPP', id)
						}

						IntervationFormData.append('ID_PROVINCE', ID_PROVINCE)
						IntervationFormData.append('commune',commune)

						$.ajax(
						{
							type: 'POST',
							url: "/pip/Processus_Investissement_Public/demande/storeIntervation",
							data:IntervationFormData,
							beforeSend: function()
							{
								$('#loading_store_lieu_inter').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
							},
							success: function(response)
							{
								$('#loading_store_lieu_inter').html("");
								const lieuText = response

								$(".table.tab_projet.d-none").removeClass("d-none")
								if(communeCountSuccess)
								{
									addProjectToTable(
										provinceText,
										ID_PROVINCE,
										communeCount,
										lieuText,
										id
										)
								}

								document.querySelector("#lieu_intervention_next").removeAttribute("disabled")
								
								$('.projet_save_button').removeAttr('disabled')
							},
							error: function(error)
							{
								console.log(error)
							},
							cache: false,
							contentType: false,
							processData: false
						})
					}
				}
			}
		}
	})

	// Avancer à l'étape 2
	document.querySelector("#js-validate-data").addEventListener("click", e =>
	{
		e.preventDefault()
		removeFeedBack()
		const target = e.currentTarget
		const parent = target.parentNode.parentNode.parentNode.parentNode.parentNode
		let success = true

		let fillablesField = fillableProject()
		fillablesField.forEach(el =>
		{
			if (parent.querySelector(el).value === "")
			{
				createElementError(parent.querySelector(el))
				success = false
			}
		})

		/**  on doit maintenant passe au page suivant */
		if (success)
		{
			removeFeedBack()
			$('#lieu_intervention').show();
			$('#tab_etude_document').hide();
			$('#projet').hide();
			$('#participants').hide();
			$('#PROFIL_CONTAINER').hide();
			$('#participants').hide();
			$('#logistique').hide();
			$('#piece').hide();

			tabs.forEach(el =>
			{
				$(el).removeClass('active')
			})

			$('#tab10').addClass('active');
		}
	})

</script>
<!--Fin Lieu d'intervention -->

<!-- Debut etude de document -->
<script>
	function get_statut()
	{
		var statut= $('#id_statut_etud').val();
		if(statut =='1')
		{
			$('#doc_reference').show()
		}else
		{
			$('#doc_reference').hide()

		}
	}

	// Afficher formulaire s'il ya étude
	$('div .etude_faisabilite').change(function()
	{
		if($(this).val() == '1')
		{
			$('#etude_document_reference').show()

			if($('.etude_document tbody').children().length)
			{
				$('.tab_etude_document_next').removeAttr('disabled')
			}
			else
			{
				$('.tab_etude_document_next').attr('disabled','disabled')
			}
		}
		else
		{
			$('#etude_document_reference').hide()
			$('.tab_etude_document_next').removeAttr('disabled')
		}
	})

	// Select statut juridique
	$('#STATUT_JURIDIQUE').change(function()
	{
		if($(this).val() == '0')
		{
			$('#AUTEUR_ORGANISME').prev().html('Nom de l\'organisme <span style="color: red">*</span>')
			$("#PAYS_ID").html('Pays <span style="color: red">*</span>')
			$('div #additional_fields').remove()
			$("#additional_fields_2").show()
			$("#additional_fields_23").show()
			$("#additional_fields_24").show()
			$('#pays_auteur').show()
			$('#AUTEUR_ORGANISME_DIV').show()

			$('#AUTEUR_ORGANISME').change(function()
			{
				$('div #additional_fields').remove()
			})
		}
		else
		{
			$('#AUTEUR_ORGANISME').prev().html('Nom de l\'auteur <span style="color: red">*</span>')
			$('div #additional_fields').remove()
			$("#additional_fields_2").show()
			$("#additional_fields_23").show()
			$("#additional_fields_24").show()
			$("#PAYS_ID").html('Nationalité <span style="color: red">*</span>')
			$('#pays_auteur').show()
			$('#AUTEUR_ORGANISME_DIV').show()
		}
	})

	$('div #supprimer_etude').click(supprimerEtude)

	// Supprimer dans la table etude document
	function supprimerEtude()
	{
		let id = $(this).data('id')
		let el = $(this)
		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/etude/cible',
			type: "POST",
			data: {
				id: id,
			},
			success: function(response)
			{
				el.parent().parent().remove()
				if($('.etude_document tbody').children().length == 0)
				{
					$('.tab_etude_document_next').attr('disabled','disabled')
					$('.etude_document').addClass('d-none')
				}
			},
			error: function(error)
			{
				console.error(error)
			}
		});
	}

	const tab2fillables = () => 
	{
		return [
			"#TITRE_ETUDE",
			"#DATE_REFERENCE",
			"#AUTEUR_ORGANISME",
			"#id_statut_etud",
			"#STATUT_JURIDIQUE",
			"#PAYS_ORIGINE"
			]
	}

	tab2fillables().forEach(el => 
	{
		$(el).change(function(){
			$('.ed_save_button').show()
		})
	})

	document.querySelector(".tab_etude_document_next").addEventListener("click", (e) =>
	{
		e.preventDefault()
		let etude = new FormData()

		if($('#A_UNE_ETUDE_NON').is(':checked'))
		{
			etude.append('A_UNE_ETUDE', 0)
		}
		else
		{
			etude.append('A_UNE_ETUDE', 1)
		}

		etude.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
		$.ajax(
		{
			type: 'POST',
			url: '/pip/Processus_Investissement_Public/demande/store/step/etude_document',
			data: etude,
			success: function(response)
			{
				removeFeedBack()
				$('#tab_contexte_projet').show();
				$('#tab_etude_document').hide();
				$('#projet').hide();
				$('#participants').hide();
				$('#PROFIL_CONTAINER').hide();
				$('#participants').hide();
				$('#logistique').hide();
				$('#piece').hide();

				tabs.forEach(el =>
				{
					$(el).removeClass('active')
				})
				$('#tab3').addClass('active');

				if($('#A_UNE_ETUDE_NON').is(':checked'))
				{
					$('.etude_document tbody').children().remove()
					$('.etude_document').addClass('d-none')
				}
			},
			error: function(error)
			{
				console.error(error)
			},
			cache: false,
			contentType: false,
			processData: false
		});
	})

	// Ajouter dans la table etude document
	document.querySelector(".ed_save_button").addEventListener("click", (ev) =>
	{
		<?php if(isset($oldValues)): ?>
			let id = $('#demande_id').val()
		<?php endif; ?>
		ev.preventDefault()
		removeFeedBack()
		const target = ev.currentTarget
		const parent = target.parentNode.parentNode.parentNode
		const edDocument = parent.querySelector("#DOC_REFERENCE")
		const titre = document.querySelector("#TITRE_ETUDE")
		const date = document.querySelector("#DATE_REFERENCE")
		const auteur = document.querySelector("#AUTEUR_ORGANISME")
		const observation = document.querySelector("#OBSERVATION")
		const adresse = document.querySelector("#adresse_organisation_id")
		const statut = document.querySelector('#STATUT_JURIDIQUE').selectedIndex
		const statutetude = document.querySelector('#id_statut_etud').selectedIndex
		const statutEtudeValue = document.querySelector('#id_statut_etud').options[statutetude].value
		const statutEtudeText = document.querySelector('#id_statut_etud').options[statutetude].text
		const size = 3000000;
		const checkFile = edDocument ? edDocument.files[0] : null
		const NIF = $('#NIF_AUTEUR').length ? $('#NIF_AUTEUR').val() : null 
		const REGISTRE = $('#REGISTRE_COMMERCIALE').length ? $('#REGISTRE_COMMERCIALE').val() : null
		const NATIONALITE_AUTEUR = $('#NATIONALITE_AUTEUR').length ? $('#NATIONALITE_AUTEUR').val() : null
		const NATIONALITE_ORGANISME = $('#NATIONALITE_ORGANISME').length ? $('#NATIONALITE_ORGANISME').val() : null
		const PAYS_ORIGINE = $('#PAYS_ORIGINE').length ? $('#PAYS_ORIGINE').val() : null

		let success = true
		const fillableCrmTab = tab2fillables()

		fillableCrmTab.forEach(el =>
		{
			if (parent.querySelector(el).value === "")
			{
				createElementError(parent.querySelector(el))
				success = false
			}
		})

		if(checkFile)
		{
			if(checkFile.type == '')
			{
				success = true
			}
			else  if (checkFile.type !== "application/pdf")
			{
				createElementError(edDocument, 'Veuillez selectionner un fichier pdf')
				success = false
			}
			console

			if (checkFile.size >= 3000000)
			{
				createElementError(edDocument, 'Taille maximum doit être de 3000000 KB')
				success = false
			}
		}

		let etude_document = $('.etude_document tbody').children()
		let count = true

		if(etude_document.length)
		{
			let text1 = ""
			let text2 = ""
			for(let i=0;i < etude_document.length;i++)
			{
				text1 = etude_document[i].getElementsByTagName('td')[0].innerText

				if(text1 == titre.value)
				{
					count = false
				}
			}
		}

		let statutText = document.querySelector('#STATUT_JURIDIQUE').options[statut].text
		let statutValue = document.querySelector('#STATUT_JURIDIQUE').options[statut].value

		if(statutValue == '0')
		{
			let statut = ['#NIF_AUTEUR','#NATIONALITE_AUTEUR']

			statut.forEach(element =>
			{
				if($(element).val() == '')
				{
					$(element).addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_champs_obligatoire') ?></div>')
					success = false;
				}
			})
		}
		else
		{
			let statut = ['#NATIONALITE_ORGANISME']

			statut.forEach(element =>
			{
				if($(element).val() == '')
				{
					$(element).addClass('is-invalid').after('<div class="invalid-feedback mt-2"><?= lang('messages_lang.message_champs_obligatoire') ?></div>')
					success = false;
				}
			})
		}

		if(success && count) 
		{
			const IntervationFormData = new FormData()
			IntervationFormData.append('titre', titre.value)

			if(checkFile)
			{
				IntervationFormData.append('files', edDocument.files[0])
				IntervationFormData.append('document', edDocument.files[0].name)
			}

			IntervationFormData.append('date', date.value)
			IntervationFormData.append('auteur', auteur.value)
			IntervationFormData.append('observation', observation.value)
			IntervationFormData.append('statut', statutValue)
			IntervationFormData.append('adresse',adresse)
			IntervationFormData.append('statut_etude',statutEtudeValue)
			if(PAYS_ORIGINE)
			{
				IntervationFormData.append('PAYS_ORIGINE', PAYS_ORIGINE)
			}

			if(NIF)
			{
				IntervationFormData.append('nif', NIF)
			}

			if(REGISTRE)
			{
				IntervationFormData.append('registre', REGISTRE)
			}

			if(NATIONALITE_AUTEUR)
			{
				IntervationFormData.append('NATIONALITE_AUTEUR', NATIONALITE_AUTEUR)
			}

			if(NATIONALITE_ORGANISME)
			{
				IntervationFormData.append('NATIONALITE_ORGANISME', NATIONALITE_ORGANISME)
			}

			if($('#demande_id').length)
			{
				IntervationFormData.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
			}

			$.ajax(
			{
				type: 'POST',
				url: "/pip/Processus_Investissement_Public/demande/storeEtudeDocument",
				data: IntervationFormData,
				success: function(response)
				{
					response = JSON.parse(response)
					let id = response[0].ID_ETUDE_DOC_REF
					let doc = response[0].DOC_REFERENCE ?? null
					addInformationEd(doc, titre.value, statutEtudeText,statutText,auteur.value, date.value,id,observation.value)

					$('#TITRE_ETUDE').val('')
					$('#DATE_REFERENCE').val('')
					$('#AUTEUR_ORGANISME').val('')
					$('#OBSERVATION').val('')
					$('#statut').val('')

					$(".table.etude_document.d-none").removeClass('d-none')
					document.querySelector(".tab_etude_document_next").removeAttribute('disabled')
				},
				error: function(error)
				{
					console.log(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	// Ajout des informations de l'Etude de document 
function addInformationEd(file, title, statutEtude,statut,auteur, date, etude,observation) 
{
	let doc = file ? `<button style="border:none;" type="button" onclick="get_doc(2,'${file}')"><span class="fa fa-file-pdf" style="color:#b30f0f;font-size: 200%;"></span></button>` : '-'
	const body = document.querySelector(".etude_document tbody")
	const tr = document.createElement('tr')
	body.append(tr)
	tr.innerHTML = `
	<td>${title}</td>
	<td>${doc}</td>
	<td>${date}</td>
	<td>${statutEtude}</td>
	<td>${statut}</td>
	<td>${auteur}</td>
	<td>${observation ? observation : '-'}</td>
	<td><div id="supprimer_etude" data-id="${etude}" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
	`
	$('div #supprimer_etude').click(supprimerEtude)
}

function get_doc(doc,file)
{
	if (doc==2)
	{
		$('#pdf2').css('display', 'block').attr('src','/'+file);
		$('#modal').modal('show');      
	}
	else
	{
		$('#modal').modal('hide');
		$('#pdf2').css('display', 'none');
	}
}

	// Retourner à l'étape 2
document.querySelector(".tab_etude_document_prev").addEventListener('click', (events) => 
{
	events.preventDefault()
	$('#lieu_intervention').show()
	$('#projet').hide();
	$('#tab_etude_document').hide();
	$('#participants').hide();
	$('#PROFIL_CONTAINER').hide();
	$('#participants').hide();
	$('#logistique').hide();
	$('#piece').hide();

	$('#tab3').removeClass('active');
	$('#tab2').removeClass('active');
	$('#tab5').removeClass('active');
	$('#tab6').removeClass('active');
	$('#tab1').removeClass('active');
	$('#tab10').addClass('active');
})
</script>
<!-- Debut etude de document -->

<!-- Debut contexte du projet -->
<script>
	$('div #supprimer_livrable').click(supprimerLivrable)

	// Supprimer dans la table demande Livrable
	function supprimerLivrable()
	{
		let id = $(this).data('id')
		let el = $(this)

		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/livrable/cible',
			type: "POST",
			data:
			{
				id: id,
			},
			success: function(response)
			{
				el.parent().parent().remove()
				let count = $('#u_demande_livrable tbody').children().length
				if(count == 0)
				{
					$('#u_demande_livrable').hide()
					$('.tab_contexte_projet_next').attr('disabled','disabled')
				}
			},
			error: function(error)
			{
				console.error(error)
			}
		});
	}

	// ajouter le bouton ajouter
	$('#DESCR_LIVRABLE').keyup(function()
	{
		if($(this).val() != '')
		{
			$('#descr_livrable_save').show()
		}
		else
		{
			$('#descr_livrable_save').hide()
		}
	})
	
	// Limit input value to 200
	$('#PATH_CONTEXTE_JUSTIFICATION').on('keydown', function()
	{
		let value = $(this).val().split(' ')

		if(value.length > 200){
			return false;
		}
	})

	const txcJamepessFillable = () => 
	{
		return [
			"#OBJECTIF_GENERAL",
			"#PATH_CONTEXTE_JUSTIFICATION",
			"#BENEFICIAIRE_PROJET",
			]
	}

	// Ajouter dans la table demande livrable
	$('#descr_livrable_save').click(function()
	{
		removeFeedBack()
		let success = true
		if(document.querySelector('#DESCR_LIVRABLE').value === "")
		{
			createElementError(document.querySelector('#DESCR_LIVRABLE'))
			success = false
		}
		else if(document.querySelector('#COUT_LIVRABLE').value === "")
		{
			createElementError(document.querySelector('#COUT_LIVRABLE'))
			success = false
		}            
		else if(document.querySelector('#DESCR_OBJECTIF').value === "") 
		{
			createElementError(document.querySelector('#DESCR_OBJECTIF'))
			success = false
		}
		else
		{
			success = true
		}

		const LivrableFormData = new FormData()
		const DESCR_LIVRABLE = $("#DESCR_LIVRABLE").val()
		const COUT_LIVRABLE = $("#COUT_LIVRABLE").val()
		const DESCR_OBJECTIF = $("#DESCR_OBJECTIF").val()

		// Vérification des doublons
		let demande_livrable = $('#u_demande_livrable tbody').children()
		let count = true

		if(demande_livrable.length)
		{
			let text1 = ""
			let text2 = ""
			for(let i=0;i < demande_livrable.length;i++)
			{
				text1 = demande_livrable[i].getElementsByTagName('td')[1].innerText

				if(text1 == DESCR_LIVRABLE)
				{
					count = false
				}
			}
		}

		if(success && count)
		{
			LivrableFormData.append('DESCR_LIVRABLE', DESCR_LIVRABLE)
			LivrableFormData.append('COUT_LIVRABLE', COUT_LIVRABLE)
			LivrableFormData.append('DESCR_OBJECTIF', DESCR_OBJECTIF)
			if($('#demande_id').length)
			{
				LivrableFormData.append('ID_DEMANDE_INFO_SUPP', $("#demande_id").val())
			}

			$.ajax(
			{
				type: "POST",
				url: "/pip/Processus_Investissement_Public/demande/storeLivrable",
				data: LivrableFormData,
				success: function(response)
				{
					let count = $('#u_demande_livrable tbody').children().length

					$('#u_demande_livrable tbody').append(`
						<tr>
						<td>${count + 1}</td>
						<td>${DESCR_OBJECTIF}</td>
						<td>${DESCR_LIVRABLE}</td>
						<td>${COUT_LIVRABLE}</td>
						<td class="flex justify-content-end"><div id="supprimer_livrable" data-id="${response}" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
						</tr>
						`)

					$('#DESCR_LIVRABLE').val('')
					$('#COUT_LIVRABLE').val('')
					$('#DESCR_OBJECTIF').val('')

					$('#descr_livrable_save').hide()

					$('div #supprimer_livrable').click(supprimerLivrable)
					$('#u_demande_livrable').show()

					$(".table.livrable_tab.d-none").removeClass('d-none')
					document.querySelector(".tab_contexte_projet_next").removeAttribute("disabled")
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	let values = ['#COUT_LIVRABLE','#CRM_livrable_an1','#CRM_livrable_an2','#CRM_livrable_an3','#total_financement','#SFP_an1','#SFP_an2','#SFP_an3']

	values.forEach(el => {
		$(el).on('input', function() {
			var value = $(this).val();
			value = value.replace(/[^0-9.]/g, '');
			value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
			$(this).val(value);
			if (/^0\d/.test(value)) {
				value = value.replace(/^0\d/, '');
				$(this).val(value);
	
			}
		})
	})

	// Avancer à l'étape 5
	document.querySelector(".tab_contexte_projet_next").addEventListener("click", (e) => 
	{
		e.preventDefault()
		const target = e.currentTarget
		removeFeedBack()
		const parent = target.parentNode.parentNode.parentNode.parentNode
		const filesTxcFile = document.querySelector("#PATH_CONTEXTE_JUSTIFICATION").value
		let success = true

		<?php if(!isset($oldValues)): ?>
			txcJamepessFillable().forEach(el =>
			{
				if (parent.querySelector(el).value === "")
				{
					createElementError(parent.querySelector(el))
					success = false
				}
			})
		<?php endif; ?>

		if(success)
		{
			let contexte = new FormData()
			contexte.append('PATH_CONTEXTE_JUSTIFICATION',$('#PATH_CONTEXTE_JUSTIFICATION').val())
			contexte.append('OBJECTIF_GENERAL',$('#OBJECTIF_GENERAL').val())
			contexte.append('BENEFICIAIRE_PROJET',$('#BENEFICIAIRE_PROJET').val())

			contexte.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/store/step/contexte',
				data: contexte,
				success: function(response)
				{
					removeFeedBack()
					$('#tab_impact_environnement_genre').show();
					$('#tab_contexte_projet').hide();
					$('#tab_etude_document').hide();
					$('#projet').hide();
					$('#participants').hide();
					$('#PROFIL_CONTAINER').hide();
					$('#participants').hide();
					$('#logistique').hide();
					$('#piece').hide();

					tabs.forEach(el =>
					{
						$(el).removeClass('active')
					})
					$('#tab4').addClass('active');
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	// Retourner à l'etape 3
	document.querySelector(".tab_contexte_projet_prev").addEventListener('click', (events) => 
	{
		events.preventDefault()
		$('#projet').hide();
		$('#tab_etude_document').show();
		$('#tab_contexte_projet').hide();
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();

		$('#tab1').removeClass('active');
		$('#tab2').addClass('active');
		$('#tab4').removeClass('active');
		$('#tab3').removeClass('active');
		$('#tab5').removeClass('active');
		$('#tab6').removeClass('active');
	})
</script>
<!-- Fin contexte du projet -->

<!-- Debut impact environnement genre -->
<script>
	// Choisir s'il y a des impacts sur l'environnement
	$('#IMPACT_ENV_OUI').change(function()
	{
		if($(this).is(':checked'))
		{
			if($('#IMPACT_GENRE_NON').is(':checked') || $('#IMPACT_GENRE_OUI').is(':checked'))
			{
				$('.tab_impact_environnement_genre_next').removeAttr('disabled')
			}
			$('#risque_env').show()
		}
	})

	$('#IMPACT_ENV_NON').change(function()
	{
		if($(this).is(':checked'))
		{
			if($('#IMPACT_GENRE_NON').is(':checked') || $('#IMPACT_GENRE_OUI').is(':checked'))
			{
				$('.tab_impact_environnement_genre_next').removeAttr('disabled')
			}
			$('#risque_env').hide()
		}
	})

	// Choisir s'il y a des impacts sur le genre
	$('#IMPACT_GENRE_OUI').change(function()
	{
		if($(this).is(':checked'))
		{
			if($('#IMPACT_ENV_NON').is(':checked') || $('#IMPACT_ENV_OUI').is(':checked'))
			{
				$('.tab_impact_environnement_genre_next').removeAttr('disabled')
			}
			$('#risque_genre').show()
		}
	})

	$('#IMPACT_GENRE_NON').change(function()
	{
		if($(this).is(':checked'))
		{
			if($('#IMPACT_ENV_OUI').is(':checked') || $('#IMPACT_ENV_NON').is(':checked'))
			{
				$('.tab_impact_environnement_genre_next').removeAttr('disabled')
			}

			$('#risque_genre').hide()
		}
	})

	// Avancer à l'étape 6
	document.querySelector(".tab_impact_environnement_genre_next").addEventListener("click", (e) =>
	{
		e.preventDefault()
		const target = e.currentTarget
		removeFeedBack()
		const parent = target.parentNode.parentNode.parentNode.parentNode
		let success = true
		if($('#IMPACT_ENV_OUI').is(':checked') && $('#IMPACT_ATTENDU_ENVIRONNEMENT').val() == '')
		{
			createElementError(document.querySelector('#IMPACT_ATTENDU_ENVIRONNEMENT'))
			success = false
		}

		if($('#IMPACT_GENRE_OUI').is(':checked') && $('#IMPACT_ATTENDU_GENRE').val() == '')
		{
			createElementError(document.querySelector('#IMPACT_ATTENDU_GENRE'))
			success = false
		}

		if(success)
		{
			let impact = new FormData()
			if($('#IMPACT_ENV_OUI').is(':checked'))
			{
				impact.append('A_UNE_IMPACT_ENV', 1)
			}
			else if($('#IMPACT_ENV_NON').is(':checked'))
			{
				impact.append('A_UNE_IMPACT_ENV', 0)
			}

			if($('#IMPACT_GENRE_NON').is(':checked'))
			{
				impact.append('A_UNE_IMPACT_GENRE', 0)
			}
			else if($('#IMPACT_GENRE_OUI').is(':checked'))
			{
				impact.append('A_UNE_IMPACT_GENRE', 1)
			}

			impact.append('IMPACT_ATTENDU_ENVIRONNEMENT', $('#IMPACT_ATTENDU_ENVIRONNEMENT').val())
			impact.append('IMPACT_ATTENDU_GENRE', $('#IMPACT_ATTENDU_GENRE').val())

			impact.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/store/step/impact',
				data: impact,
				success: function(response)
				{
					removeFeedBack()
					$('#risque_projet_update').show()
					$('#crm_livrable').hide()
					$('#tab_impact_environnement_genre').hide();
					$('#tab_contexte_projet').hide();
					$('#tab_etude_document').hide();
					$('#projet').hide();
					$('#participants').hide();
					$('#PROFIL_CONTAINER').hide();
					$('#participants').hide();
					$('#logistique').hide();
					$('#piece').hide();

					tabs.forEach(el =>
					{
						$(el).removeClass('active')
					})

					$('#tab14').addClass('active')
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	//Retourner à l'étape 4
	document.querySelector(".tab_impact_environnement_genre_prev").addEventListener('click', (events) =>
	{
		events.preventDefault()
		$('#projet').hide();
		$('#tab_impact_environnement_genre').hide();
		$('#tab_etude_document').hide();
		$('#tab_contexte_projet').show();
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();
		$('#crm_objectif_general').hide();
		tabs.forEach(el =>
		{
			$(el).removeClass('active')
		})

		$('#tab3').addClass('active');
	})
</script>
<!-- Fin impact environnement genre -->

<!-- Debut risques de projet -->
<script>
	$('#RISQUE_PROJET_OUI').change(function()
	{
		if($(this).is(':checked'))
		{
			$('#risque_proj').show()
			$('.RISQUE_PROJET_NEXT').attr('disabled','disabled')
		}
		else
		{
			$('#risque_proj').hide()
			$('.RISQUE_PROJET_NEXT').removeAttr('disabled')
		}
	})

	$('#RISQUE_PROJET_NON').change(function()
	{
		if($(this).is(':checked'))
		{
			$('#risque_proj').hide()
			$('.RISQUE_PROJET_NEXT').removeAttr('disabled')
		}
		else
		{
			$('#risque_proj').show()
			$('.RISQUE_PROJET_NEXT').attr('disabled','disabled')
		}
	})

	$('.RISQUE_PROJET_PREV').click(function()
	{
		$('#projet').hide();
		$('#tab_impact_environnement_genre').show();
		$('#tab_etude_document').hide();
		$('#tab_contexte_projet').hide();
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();
		$('#risque_projet_update').hide();

		tabs.forEach(el => 
		{
			$(el).removeClass('active')
		})

		$('#tab4').addClass('active');
	})

	const risqueProjetFillable = ['#RISQUE_PROJET_VALEUR','#RISQUE_PROJET_MITIGATION']
	$('.RISQUE_PROJET_SAVE').click(function(e)
	{
		e.preventDefault()
		let nom = $('#RISQUE_PROJET_VALEUR').val()
		let mitigation = $('#RISQUE_PROJET_MITIGATION').val()
		let success = true
		risqueProjetFillable.forEach(el =>
		{
			if (document.querySelector(el).value === "")
			{
				createElementError(document.querySelector(el))
				success = false
			}
			else
			{
				success = true
			}
		})

		if(success)
		{
			let risqueProjet = new FormData()
			risqueProjet.append('nom_risque',nom)
			risqueProjet.append('nom_mesure_mitigation',mitigation)
			risqueProjet.append('ID_DEMANDE_INFO_SUPP',$('#demande_id').val()) 
			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/storeRisque/risque_projet',
				data: risqueProjet,
				success: function(response)
				{
					removeFeedBack()
					addInfoRisque(nom,mitigation,response)
					$('.RISQUE_PROJET_NEXT').removeAttr('disabled')
					$('.RISQUE_PROJET_TABLE').removeClass('d-none')

					$('#RISQUE_PROJET_VALEUR').val('')
					$('#RISQUE_PROJET_MITIGATION').val('')
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	function supprimerRisqueProjet()
	{
		let id = $(this).data('id')
		let el = $(this)
		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/risque/risque_projet',
			type: "POST",
			data:
			{
				id: id,
			},
			success: function(response)
			{
				el.parent().parent().remove()
				if($('.RISQUE_PROJET_TABLE tbody').children().length == 0)
				{
					$('.RISQUE_PROJET_NEXT').attr('disabled','disabled')
					$('.RISQUE_PROJET_TABLE').addClass("d-none")
				}
			},
			error: function(error)
			{
				console.error(error)
			}
		});

		return false;
	}
	$('div #supprimerRisqueProjet').click(supprimerRisqueProjet)

	function addInfoRisque(nom,mitigation,id)
	{
		$('.RISQUE_PROJET_TABLE tbody').append(`
			<tr>
			<td>${nom}</td>
			<td>${mitigation}</td>
			<td><a id="supprimerRisqueProjet" data-id="${id}" class="btn btn-danger"><i class="fa fa-close"></i></a></td>
			</tr>
			`)

		$('div #supprimerRisqueProjet').click(supprimerRisqueProjet)
	}

	$('.RISQUE_PROJET_NEXT').click(function()
	{
		let ID_DEMANDE_INFO_SUPP = $('#demande_id').val()
		let risqueProjetData = new FormData()
		//Enregistrement risques
		if($('#RISQUE_PROJET_OUI').is(':checked'))
		{
			risqueProjetData.append('RISQUE_PROJET',1)
		}
		else
		{
			risqueProjetData.append('RISQUE_PROJET',0)
		}
		risqueProjetData.append('ID_DEMANDE_INFO_SUPP',ID_DEMANDE_INFO_SUPP)
		$.ajax(
		{
			type: 'POST',
			url: '/pip/Processus_Investissement_Public/demande/store/step/risque_projet',
			data: risqueProjetData,
			success: function(response)
			{
				if($('#cadre_mesure_livrable tbody').children().length == 0)
				{
					getLivrable()
				}
				else
				{
					$('.crm_livrable_next').removeAttr('disabled')
				}
			},
			error: function(error)
			{
				console.error(error)
			},
			cache: false,
			contentType: false,
			processData: false
		})
	})

	// Affichage des livrables
	function getLivrable()
	{
		let ID_DEMANDE_INFO_SUPP = $('#demande_id').val()
		$.ajax(
		{
			type: "GET",
			url: "/pip/Processus_Investissement_Public/demande/livrable/cmr/"+ID_DEMANDE_INFO_SUPP,
			dataType: 'JSON',
			success: function(response)
			{  
				removeFeedBack()
				$('#risque_projet_update').hide()
				$('#projet').hide();
				$('#crm_livrable').show()
				$('#tab_impact_environnement_genre').hide();
				$('#tab_etude_document').hide();
				$('#tab_contexte_projet').hide();
				$('#participants').hide();
				$('#PROFIL_CONTAINER').hide();
				$('#participants').hide();
				$('#logistique').hide();
				$('#piece').hide();

				tabs.forEach(el =>
				{
					$(el).removeClass('active')
				})

				$('#tab13').addClass('active');

				if(!$('#crm_livrable_input').length)
				{
					$('#indicateur_mesure_livrable').parent().parent().before(`
						<div class="col-12 col-md-6" id="crm_livrable_input">
						<div class="form-group">
						<label for="ID_LIVRABLE">Livrable <font color="red">*</font></label>
						<select class="form-control" name="ID_LIVRABLE" id="ID_LIVRABLE">${response}</select>
						</div>
						</div>
						`)
				}
				else
				{
					$('div #crm_livrable_input').html(`
						<div class="form-group">
						<label for="ID_LIVRABLE">Livrable <font color="red">*</font></label>
						<select class="form-control" name="ID_LIVRABLE" id="ID_LIVRABLE">${response}</select>
						</div>
						`)
				}
			},
			error: function(error)
			{
				console.error(error)
			}
		})
	}
</script>
<!-- Fin risques de projet -->

<!-- Debut cadre mesure des résultats -->
<script>
	$('div #supprimer_crm_livrable').click(supprimerCRMLivrable)

	// Supprimer dans la table cadre mesure résultat livrable
	function supprimerCRMLivrable()
	{
		let id = $(this).data('id')
		let el = $(this)
		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/crm/livrable',
			type: "POST",
			data:
			{
				id: id,
			},
			success: function(response)
			{
				el.parent().parent().remove()

				if($('.cadre_mesure_livrable tbody').children().length == 0)
				{
					$('.cadre_mesure_livrable').hide()
				}

				getLivrable()
			},
			error: function(error)
			{
				console.error(error)
			}
		});
	}

	const crmLivrableFillable = () =>
	{
		return [
			"#ID_LIVRABLE",
			"#indicateur_mesure_livrable",
			"#unite_mesure_livrable",
			"#CRM_livrable_an1",
			"#CRM_livrable_an2",
			"#CRM_livrable_an3",
			"#reference_livrable",
			"#cumulative_id"
			]
	}

	crmLivrableFillable().forEach(el =>
	{
		$(el).change(function()
		{
			$('.cm_livrable_save_button').parent().show()
		})
	})

	// Avancer à l'étape 8
	document.querySelector(".crm_livrable_next").addEventListener("click", (e) =>
	{
		let success = true
		e.preventDefault()
		const target = e.currentTarget
		removeFeedBack()

		if(success)
		{
			removeFeedBack()
			$('#bpl').show();
			$("#crm_objectif_general").hide();
			$("#crm_objectif_specifique").hide();
			$("#crm_livrable").hide();
			$('#tab_impact_environnement_genre').hide();
			$('#tab_contexte_projet').hide();
			$('#tab_etude_document').hide();
			$('#projet').hide();
			$('#participants').hide();
			$('#PROFIL_CONTAINER').hide();
			$('#participants').hide();
			$('#logistique').hide();
			$('#piece').hide();

			tabs.forEach(el =>
			{
				$(el).removeClass('active')
			})
			$('#tab7').addClass('active');
			let id = $('#demande_id').val()

			// Ajouter livrable à budget projet livrable
			if($('#BPL_livrable').children().length == 0 || $('#SFP_livrable').children().length == 0)
			{
				$.ajax(
				{
					type: 'GET',
					url: '/pip/Processus_Investissement_Public/demande/livrable/bpl/'+id,
					dataType: "JSON",
					success: function(response)
					{
						$('#BPL_livrable').html(response)
						$('#SFP_livrable').html(response)
					},
					error: function(error)
					{
						console.error(error)
					}
				})
			}
		}
	})

	// Retourner à l'étape 6
	document.querySelector(".crm_livrable_prev").addEventListener("click", (e) =>
	{
		$('#risque_projet_update').show()
		$('#tab_impact_environnement_genre').hide();
		$("#crm_livrable").hide();
		$('#bpl').hide();
		$('#tab_contexte_projet').hide();
		$('#tab_etude_document').hide();
		$('#projet').hide();
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();

		tabs.forEach(el =>
		{
			$(el).removeClass('active')
		})

		$('#tab14').addClass('active');
	})

	function addInformationCmLivrable(Indicateur_mesure, Unite_mesure, Annee_1, Annee_2, Annee_3,total3,crm,insert,cumulative)
	{
		const body = document.querySelector(".cadre_mesure_livrable tbody")
		const tr = document.createElement('tr')

		body.append(tr)
		tr.innerHTML = `
		<td>${insert}</td>
		<td>${Indicateur_mesure}</td>
		<td>${cumulative}</td>
		<td>${Unite_mesure}</td>
		<td>${Annee_1}</td>
		<td>${Annee_2}</td>
		<td>${Annee_3}</td>
		<td>${new Intl.NumberFormat('de-DE').format(total3)}</td>
		<td><div id="supprimer_crm_livrable" data-id="${crm}" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
		`

		$('div #supprimer_crm_livrable').click(supprimerCRMLivrable)
	}

	// Ajouter dans la table cadre mesure résultat livrable
	document.querySelector(".cm_livrable_save_button").addEventListener('click', (ev) =>
	{
		let id = $('#demande_id').val()
		ev.preventDefault()
		removeFeedBack()
		const target = ev.currentTarget

		const unite_mesure_livrable = parseInt(document.querySelector("#unite_mesure_livrable").selectedIndex)
		const cumulative_livrable= parseInt(document.querySelector("#cumulative_id").selectedIndex)
		let CRM_livrable_an1 = document.querySelector("#CRM_livrable_an1").value
		let CRM_livrable_an2 = document.querySelector("#CRM_livrable_an2").value
		let CRM_livrable_an3 = document.querySelector("#CRM_livrable_an3").value
		CRM_livrable_an1 = CRM_livrable_an1.replaceAll(' ','')
		CRM_livrable_an2 = CRM_livrable_an2.replaceAll(' ','')
		CRM_livrable_an3 = CRM_livrable_an3.replaceAll(' ','')
		const CRM_livrable_annee_1 = document.querySelector("#CRM_livrable_annee_1").value
		const CRM_livrable_annee_2 = document.querySelector("#CRM_livrable_annee_2").value
		const CRM_livrable_annee_3 = document.querySelector("#CRM_livrable_annee_3").value

		let indicateurTextLivrable = document.querySelector("#indicateur_mesure_livrable").value
		let uniteTextLivrable = document.querySelector("#unite_mesure_livrable").options[unite_mesure_livrable].text
		let cumulativeText= document.querySelector("#cumulative_id").options[cumulative_livrable].text


		let success = true

		<?php if(!isset($oldValues)): ?>
			crmLivrableFillable().forEach(el =>
			{
				if (document.querySelector(el).value === "")
				{
					createElementError(document.querySelector(el))
					success = false
				} 
			})
		<?php endif; ?>

		let total = parseInt(CRM_livrable_an1) + parseInt(CRM_livrable_an2) + parseInt(CRM_livrable_an3)
		let cadre_mesure = $('.cadre_mesure_livrable tbody').children()
		let count = true

		if(cadre_mesure.length)
		{
			let text1 = ""
			let text2 = ""
			let text3 = ""
			let text4 = ""
			let text5 = ""
			let text6 = ""
			let text7 = ""

			for(let i=0;i < cadre_mesure.length;i++)
			{
				text1 = cadre_mesure[i].getElementsByTagName('td')[1].innerText
				text2 = cadre_mesure[i].getElementsByTagName('td')[2].innerText

				if(text1 == indicateurTextLivrable && text2 == uniteTextLivrable) 
				{
					count = false
					$('#crm_livrable').prepend(`
						<div class="alert alert-danger my-3"><?= lang('messages_lang.message_indicateur_unite') ?></div>
						`)
					$('.alert.alert-danger').delay(2000).fadeOut('slow');
				}
			}
		}

		if(CRM_livrable_annee_1 == CRM_livrable_annee_2 || CRM_livrable_annee_1 == CRM_livrable_annee_3 || CRM_livrable_annee_2 == CRM_livrable_annee_3)
		{
			success = false
			$('#crm_livrable').prepend(`
				<div class="alert alert-danger my-3"><?= lang('messages_lang.message_annee_budget') ?></div>
				`)
			$('.alert.alert-danger').delay(2000).fadeOut('slow');
		}

		let total3 = parseFloat(CRM_livrable_an1) + parseFloat(CRM_livrable_an2) + parseFloat(CRM_livrable_an3)

		//cas pourcentage dimumition et nombre duminiton
		if(($('#unite_mesure_livrable').val() == 1  && $('#cumulative_id').val() ==1) || ($('#unite_mesure_livrable').val() == 2  && $('#cumulative_id').val() ==1))
		{	 
			if(parseFloat(CRM_livrable_an1)> parseFloat(CRM_livrable_an2) && parseFloat(CRM_livrable_an2) > parseFloat(CRM_livrable_an3))
			{
				// verifier si la valeur anne 1 n est superieure a 100 cas pourcentage
				if($('#unite_mesure_livrable').val() == 1 && parseFloat(CRM_livrable_an1)> 100)
				{	
					$('#crm_livrable').prepend(`
						<div class="alert alert-danger my-3"><?= lang('messages_lang.message_err_annee') ?></div>
						`)
					$('.alert.alert-danger').delay(2000).fadeOut('slow');
					success=false
				}
			}
			else
			{
				$('#crm_livrable').prepend(`
					<div class="alert alert-danger my-3"><?= lang('messages_lang.message_err_inf') ?></div>
					`)
				$('.alert.alert-danger').delay(2000).fadeOut('slow');
				success=false
			}
			total3 =  parseFloat(CRM_livrable_an3)
		}
		else if( ($('#unite_mesure_livrable').val() == 1  && $('#cumulative_id').val() ==2) || ($('#unite_mesure_livrable').val() == 2  && $('#cumulative_id').val() ==2))
		{
			//cas augmentation
			if(parseFloat(CRM_livrable_an1) < parseFloat(CRM_livrable_an2) && parseFloat(CRM_livrable_an2) < parseFloat(CRM_livrable_an3))
			{	
				if($('#unite_mesure_livrable').val() == 1 && parseFloat(CRM_livrable_an3) > 100 ) 
				{
					$('#crm_livrable').prepend(`
						<div class="alert alert-danger my-3"><?= lang('messages_lang.message_err_augmentation') ?></div>
						`)
					$('.alert.alert-danger').delay(2000).fadeOut('slow');
					success=false
				}
				total3 =  parseFloat(CRM_livrable_an3)
			}
			else
			{
				$('#crm_livrable').prepend(`
					<div class="alert alert-danger my-3"><?= lang('messages_lang.message_err_sup') ?></div>
					`)
				$('.alert.alert-danger').delay(2000).fadeOut('slow');
				success=false
			}
		}
		else if(($('#unite_mesure_livrable').val() == 1  && $('#cumulative_id').val() ==3) || ($('#unite_mesure_livrable').val() == 2  && $('#cumulative_id').val() ==3))
		{
			//cas sommation
			if(($('#unite_mesure_livrable').val() ==1 && $('#cumulative_id').val() ==3  ) && total3 > 100)
			{
				$('#crm_livrable').prepend(`
					<div class="alert alert-danger my-3"><?= lang('messages_lang.message_err_total') ?></div>
					`)
				$('.alert.alert-danger').delay(2000).fadeOut('slow');
				success=false
			}
			total3=parseFloat(CRM_livrable_an1) + parseFloat(CRM_livrable_an2) + parseFloat(CRM_livrable_an3)
		}

		if(success && count)
		{
			removeFeedBack()
			let insert = "";
			const IntervationFormData = new FormData()
			IntervationFormData.append('indicateur_mesure', indicateurTextLivrable)
			IntervationFormData.append('unite_mesure', document.querySelector("#unite_mesure_livrable").value)
			IntervationFormData.append('cumulative', document.querySelector("#cumulative_id").value)
			IntervationFormData.append('CRM_an1', CRM_livrable_an1)
			IntervationFormData.append('CRM_an2', CRM_livrable_an2)
			IntervationFormData.append('CRM_an3', CRM_livrable_an3)
			IntervationFormData.append('total3', total3)
			IntervationFormData.append('reference', $('#reference_livrable').val())
			IntervationFormData.append('CRM_livrable_annee_1', CRM_livrable_annee_1)
			IntervationFormData.append('CRM_livrable_annee_2', CRM_livrable_annee_2)
			IntervationFormData.append('CRM_livrable_annee_3', CRM_livrable_annee_3)

			let livrable = parseInt(document.querySelector("#ID_LIVRABLE").selectedIndex)
			insert = document.querySelector("#ID_LIVRABLE").options[livrable].text
			IntervationFormData.append('INSERT', document.querySelector("#ID_LIVRABLE").options[livrable].value)

			if($('#demande_id').length)
			{
				IntervationFormData.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
			}

			$.ajax(
			{
				type: 'POST',
				url: "/pip/Processus_Investissement_Public/demande/storeCmr/livrable",
				data: IntervationFormData,
				success: function(response)
				{
					let id = response

					// Show Saved Information in table
					addInformationCmLivrable(
						indicateurTextLivrable,uniteTextLivrable,CRM_livrable_an1, CRM_livrable_an2, CRM_livrable_an3,total3,id,insert,cumulativeText
						)

					$('#indicateur_mesure_livrable').val('')
					$('#reference_livrable').val('')
					$('#CRM_livrable_an1').val('')
					$('#CRM_livrable_an2').val('')
					$('#CRM_livrable_an3').val('')

					$(".table.cadre_mesure_livrable").show()
					document.querySelector('#ID_LIVRABLE').remove(document.querySelector('#ID_LIVRABLE').selectedIndex)

					document.querySelector(".crm_livrable_next").removeAttribute("disabled")
				},
				error: function(error)
				{
					console.log(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})
</script>
<!-- Fin cadre mesure des résultats -->

<!-- Debut budget du projet par livrable -->
<script>
	$('#BPL_livrable').change(function()
	{
		let id = $(this).val()
		$.ajax(
		{
			type: 'GET',
			url: '/pip/Processus_Investissement_Public/demande/cout_livrable/'+id,
			success: function(response)
			{
				$('#cout_unitaire').val(response)
				
			},
			error: function(error)
			{
				console.error(error)
			}
		})
	})

	$('div #supprimer_nomen').click(supprimerNomen)

	// Supprimer dans la table pip_budget_livrable_nomenclature_budgetaire
	function supprimerNomen()
	{
		let id = $(this).data('id')
		let el = $(this)

		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/nomenclature/cible',
			type: "POST",
			data:
			{
				id: id,
			},
			success: function(response)
			{
				if($('.bpl_projet tbody').children().length == 0)
				{
					$('.bpl_next').attr('disabled','disabled')
					$('.bpl_projet').addClass('d-none')
				}
				el.parent().parent().remove()
			},
			error: function(error)
			{
				console.error(error)
			}
		});
	}

	const bplFillables = () =>
	{
		return [
			"#BPL_livrable",
			"#year_un",
			"#year_deux",
			"#year_trois",
			"#cout_unitaire",
			]
	}

	bplFillables().forEach(el =>
	{
		$(el).change(function()
		{
		})
	})

	$('#nom_menclature').change(function()
	{
		let id = $(this).val()
		showNomenclature(id)
	})

	// Avancer à l'étape 10
	document.querySelector(".bpl_next").addEventListener("click", (e) =>
	{
		e.preventDefault()
		removeFeedBack()
		const target = e.currentTarget
		let success = true

		if(success)
		{
			removeFeedBack()
			$('#sfp').show();
			$('#tab_impact_environnement_genre').hide();
			$('#bpl').hide();
			$('#tab_contexte_projet').hide();
			$('#tab_etude_document').hide();
			$('#projet').hide();
			$('#participants').hide();
			$('#PROFIL_CONTAINER').hide();
			$('#participants').hide();
			$('#logistique').hide();
			$('#piece').hide();

			tabs.forEach(el =>
			{
				$(el).removeClass('active')
			})

			$('#tab8').addClass('active');
		}
	})

	const objectifFillables = ["#PATH_CONTEXTE_JUSTIFICATION","#OBJECTIF_GENERAL","#DESCR_OBJECTIF","#DESCR_LIVRABLE","#BENEFICIAIRE_PROJET"]
	// retourner à l'étape 8
	document.querySelector(".bpl_prev").addEventListener("click", (ev) =>
	{
		ev.preventDefault()
		$('#bpl').hide();
		$("#crm_livrable").show();
		$("#crm").hide();
		$('#tab_impact_environnement_genre').hide();
		$('#tab_contexte_projet').hide();
		$('#tab_etude_document').hide();
		$('#projet').hide();
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();


		tabs.forEach(el =>
		{
			$(el).removeClass('active')
		})

		$('#tab13').addClass('active');
	})

	// Retourner à l'etape 9
	document.querySelector(".sfp_prev").addEventListener('click', (e) =>
	{
		e.preventDefault()
		$('#bpl').show();
		$('#sfp').hide();
		$("#txc").hide();
		$("#crm").hide();
		$('#tab_impact_environnement_genre').hide();
		$('#tab_contexte_projet').hide();
		$('#tab_etude_document').hide();
		$('#projet').hide();
		$('#participants').hide();
		$('#PROFIL_CONTAINER').hide();
		$('#participants').hide();
		$('#logistique').hide();
		$('#piece').hide();

		$('#tab1').removeClass('active');
		$('#tab2').removeClass('active');
		$('#tab3').removeClass('active');
		$('#tab4').removeClass('active');
		$('#tab5').removeClass('active');
		$('#tab6').removeClass('active');
		$('#tab7').addClass('active');
		$('#tab9').removeClass('active');
		$('#tab8').removeClass('active');
	})
</script>
<!-- Fin budget du projet par livrable -->

<!-- Debut source de financement -->
<script>
	$('#EST_CO_FINANCE_OUI').change(function ()
	{
		if($(this).is(':checked'))
		{
			$('#financement').show()
			$('.sfp_save_button ').show()
			$('.save_all').attr('disabled','disabled')
		}
	})

	$('#EST_CO_FINANCE_NON').change(function ()
	{
		if($(this).is(':checked'))
		{
			$('#financement').show()
			$('.sfp_save_button ').hide()
			$('.save_all').removeAttr('disabled')
		}
	})

	$('#total_financement').keyup(function()
	{
		let devise = document.querySelector('#devise_financement').selectedIndex
		let value = document.querySelector('#devise_financement').options[devise].dataset.taux
		let total = $('#total_financement').val()
		total = total.replaceAll(' ','')
		let bif = (parseFloat(total) * parseFloat(value)) 
		if(isNaN(bif)){
			$('#total_financement_bif').val("")
		}else{
			bif = bif.toString()
			bif = bif.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
			$('#total_financement_bif').val(bif)
		}

	})

	// Avancer à l'étape 10
	$('.save_all').click(function()
	{
		removeFeedBack()
		let success = true
		let SFPFillables = ['bailleur','devise_financement','total_financement','total_financement_bif','SFP_an1','SFP_an2','SFP_an3']

		let total_financement = $('#total_financement').val()
		let total_financement_bif = $('#total_financement_bif').val()
		let SFP_an1 = $('#SFP_an1').val()
		let SFP_an2 = $('#SFP_an2').val()
		let SFP_an3 = $('#SFP_an3').val()
		SFP_an1=SFP_an1.replaceAll(' ','')
		SFP_an2=SFP_an2.replaceAll(' ','')
		SFP_an3=SFP_an3.replaceAll(' ','')
		total_financement_bif=total_financement_bif.replaceAll(' ','')
		total_financement=total_financement.replaceAll(' ','')

		let annee_sfp_1 = $('#annee_sfp_1').val()
		let annee_sfp_2 = $('#annee_sfp_2').val()
		let annee_sfp_3 = $('#annee_sfp_3').val()
		

		let total = parseFloat(SFP_an1) + parseFloat(SFP_an2) + parseFloat(SFP_an3)

		if($('#EST_CO_FINANCE_NON').is(':checked')){
			Tab10Fillable().forEach(el =>
			{
				if (document.querySelector(el).value === "")
				{
					createElementError(document.querySelector(el))
					success = false
				}
			})
		}

		<?php if(!isset($oldValues)): ?>
			if($('#EST_CO_FINANCE_NON').is(':checked'))
			{
				if(total != total_financement)
				{
					$('#total_financement').addClass('is-invalid').after(`
						<div class="invalid-feedback"><?= lang('messages_lang.message_erreur_sfp') ?></div>
						`)
					success = false
				}
			}
		<?php endif; ?>

		if(success)
		{
			let sfp = new FormData()

			if($('#EST_CO_FINANCE_OUI').is(':checked'))
			{
				sfp.append('EST_CO_FINANCE', 1)
			}
			else
			{
				sfp.append('EST_CO_FINANCE', 0)
				let bailleur = document.querySelector('#bailleur').selectedIndex;
				let bailleurValue = document.querySelector('#bailleur').options[bailleur].value
				let bailleurText = document.querySelector('#bailleur').options[bailleur].text
	
				let selectedDevise = document.querySelector('#devise_financement').selectedIndex;
				let deviseValue = document.querySelector('#devise_financement').options[selectedDevise].value
				let deviseText = document.querySelector('#devise_financement').options[selectedDevise].text
	
				sfp.append('bailleur', bailleurValue)
				sfp.append('devise_financement', deviseValue)
				sfp.append('total_financement', $('#total_financement').val())
				sfp.append('total_financement_bif', $('#total_financement_bif').val())
				sfp.append('SFP_an1', $('#SFP_an1').val())
				sfp.append('SFP_an2', $('#SFP_an2').val())
				sfp.append('SFP_an3', $('#SFP_an3').val())
	
				sfp.append('annee_sfp_1', $('#annee_sfp_1').val())
				sfp.append('annee_sfp_2', $('#annee_sfp_2').val())
				sfp.append('annee_sfp_3', $('#annee_sfp_3').val())
			}
			
			sfp.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
			$.ajax(
			{
				type: 'POST',
				url: '/pip/Processus_Investissement_Public/demande/store/step/source_financement',
				data: sfp,
				success: function(response)
				{
					tabs.forEach(el =>
					{
						$(el).removeClass('active')
					})

					$('#tab11').addClass('active')

					$('#sfp').hide()
					$('#observation_complementaire').show()
					$('#bpl').hide()
				},
				error: function(error)
				{
					console.error(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})
		}
	})

	$('div #supprimer_sfp').click(supprimerSFP)

	// Supprimer dans la table pip_source_financement_bailleur
	function supprimerSFP()
	{
		let id = $(this).data('id')
		let el = $(this)
		$.ajax(
		{
			url: '/pip/Processus_Investissement_Public/demande/delete/sfp/cible',
			type: "POST",
			data:
			{
				id: id,
			},
			success: function(response)
			{
				el.parent().parent().remove()
				if($('.sfp_tab tbody').children().length == 0)
				{
					$('.save_all').attr('disabled','disabled')
					$('.sfp_tab').addClass('d-none')
				}
			},
			error: function(error)
			{
				console.error(error)
			}
		});
	}

	const Tab10Fillable = () =>
	{
		return ["#devise_financement", "#total_financement", "#bailleur", "#SFP_an1", "#SFP_an2", "#SFP_an3"]
	}

	// Afficher le bouton ajouter onchange
	Tab10Fillable().forEach(el =>
	{
		if($('#EST_CO_FINANCE_OUI').is(':checked'))
		{
			$(el).change(function()
			{
				$('.sfp_save_button').show()
			})
		}
	})

	// Ajouter à la table pip_source_financement_bailleur
	const checksfp = (ev) =>
	{
		ev.preventDefault()
		removeFeedBack()
		let success = true
		Tab10Fillable().forEach(el =>
		{
			if (document.querySelector(el).value === "")
			{
				createElementError(document.querySelector(el))
				success = false
			}
		})

		let selectedBailleur = document.querySelector('#bailleur').selectedIndex;
		let selectedBailleurText = document.querySelector('#bailleur').options[selectedBailleur].text;

		let bailleur = document.querySelector("#bailleur").value;
		let an1 = document.querySelector("#SFP_an1").value;
		let an2 = document.querySelector("#SFP_an2").value;
		let an3 = document.querySelector("#SFP_an3").value;
		an1=an1.replaceAll(' ','')
		an2=an2.replaceAll(' ','')
		an3=an3.replaceAll(' ','')
		let selectedDevise = document.querySelector('#devise_financement').selectedIndex;
		let deviseText = document.querySelector('#devise_financement').options[selectedDevise].text;
		let devise = document.querySelector("#devise_financement").value;
		let total_financement = document.querySelector("#total_financement").value;
		total_financement=total_financement.replaceAll(' ','')
		let total = parseFloat(an1) + parseFloat(an2) + parseFloat(an3);

		let annee_sfp_1 = document.querySelector("#annee_sfp_1").value;
		let annee_sfp_2 = document.querySelector("#annee_sfp_2").value;
		let annee_sfp_3 = document.querySelector("#annee_sfp_3").value;

		let taux = parseInt(document.querySelector("#devise_financement").selectedIndex);
		let taux_devise = document.querySelector("#devise_financement").options[taux].dataset.taux;

		let totalBIF = parseInt(total_financement * taux_devise);

		let id_demande = $('#demande_id').val()

		let sfp_tab = $('.sfp_tab tbody').children()
		let count = true

		if(sfp_tab.length)
		{
			let text1 = ""
			let text2 = ""
			for(let i=0;i < sfp_tab.length;i++)
			{
				text1 = sfp_tab[i].getElementsByTagName('td')[0].innerText

				if(text1 ==  selectedBailleurText)
				{
					count = false
				}
			}
		}

		if(total != total_financement)
		{
			$('#total_financement').addClass('is-invalid').after(`<div class="invalid-feedback"><?= lang('messages_lang.message_erreur_total_sfp') ?></div>`)
			success = false
		}

		if(annee_sfp_1 == annee_sfp_2 || annee_sfp_2 == annee_sfp_3 || annee_sfp_1 == annee_sfp_3)
		{
			$('#sfp').prepend(`
				<div class="alert alert-danger my-3"><?= lang('messages_lang.message_erreur_annee_sfp') ?></div>
				`)
			$('.alert.alert-danger').delay(2000).fadeOut('slow');
			success = false
		}

		if(success && count)
		{
			const SFPFormData = new FormData()
			SFPFormData.append('SFP_bailleur',bailleur)
			SFPFormData.append('SFP_an1',an1)
			SFPFormData.append('SFP_an2',an2)
			SFPFormData.append('SFP_an3',an3)
			SFPFormData.append('DEVISE',devise)

			SFPFormData.append('annee_sfp_1',annee_sfp_1)
			SFPFormData.append('annee_sfp_2',annee_sfp_2)
			SFPFormData.append('annee_sfp_3',annee_sfp_3)
			SFPFormData.append('total_financement',total_financement)
			SFPFormData.append('totalBIF',totalBIF)

			if($('#demande_id').length)
			{
				SFPFormData.append('ID_DEMANDE_INFO_SUPP', $('#demande_id').val())
			}

			$.ajax(
			{
				type: 'POST',
				url: "/pip/Processus_Investissement_Public/demande/storeSFP",
				data: SFPFormData,
				success: function(response)
				{
					let id = response
					let sfp_ths = $('.sfp_tab thead tr').children()

					addToSFPTable(selectedBailleurText,an1,an2,an3,deviseText,total,totalBIF,id)

					$('#bailleur').prop('selectedIndex',-1)
					$('#SFP_an1').val('')
					$('#SFP_an2').val('')
					$('#SFP_an3').val('')
					$('#devise_financement').prop('selectedIndex',-1)
					$('#total_financement').val('')
					$('#total_financement_bif').val('')

					$('.save_all').removeAttr('disabled')
					$("table.sfp_tab.d-none").removeClass('d-none')
				},
				error: function(error)
				{
					console.log(error)
				},
				cache: false,
				contentType: false,
				processData: false
			})

		}
	}

	$('.sfp_save_button').click(checksfp)

	// Ajouter au tableau
	const addToSFPTable = (bailleur,anne1,anne2,anne3,devise,total,totalBif,delete_button) =>
	{
		const body = document.querySelector(".sfp_tab tbody")
		const tr = document.createElement('tr')
		body.append(tr)
		tr.innerHTML = `
		<td>${bailleur}</td>
		<td>${new Intl.NumberFormat('de-DE').format(anne1)}</td>
		<td>${new Intl.NumberFormat('de-DE').format(anne2)}</td>
		<td>${new Intl.NumberFormat('de-DE').format(anne3)}</td>
		<td>${devise}</td>
		<td>${new Intl.NumberFormat('de-DE').format(total)}</td>
		<td>${new Intl.NumberFormat('de-DE').format(totalBif)}
		<td><div id="supprimer_sfp" data-id="${delete_button}" class="btn btn-danger"><i class="fa fa-close"></i></div></td>
		`
		$('div #supprimer_sfp').click(supprimerSFP)
	}
</script>
<!-- Fin source de financement -->

<!-- Debut Observation Complémentaire -->
<script>
	// Retourner à l'étape 10
	$('.observation_prev').click(function()
	{
		$('#tab11').removeClass('active')
		$('#tab7').removeClass('active')
		$('#tab8').addClass('active')

		$('#sfp').show()
		$('#observation_complementaire').hide()
		$('#bpl').hide()
	})
</script>
<!-- Fin Observation Complémentaire -->