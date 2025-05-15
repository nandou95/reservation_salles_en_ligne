<?php
/* @autor nandou@mediabox.bi 71483905*/
namespace Config;
use App\Controllers\ShoppingCart;
$routes = Services::routes();

/* Router Setup */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Login_Ptba');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(function()
{
	return view('Page404'); 
});

//For internationalisation
$routes->get('lang/{locale}', 'Language::index');
#################### Debut Login #################
$routes->match(['get', 'post'], '/', 'Login_Ptba::index');
$routes->match(['get', 'post'], 'Login_Ptba', 'Login_Ptba::index');
$routes->post('Login_Ptba/login', 'Login_Ptba::login');
$routes->get('Login_Ptba/do_logout', 'Login_Ptba::do_logout');
$routes->get('Login_Ptba/homepage', 'Login_Ptba::homepage');
// $routes->post('Login_Ptba/check_mail', 'Login_Ptba::check_mail');
#################### Fin Login ###################

############## Debut Changement de mot de passe ######
$routes->match(['get', 'post'], 'Recover_pwd', 'Recover_pwd::index');
$routes->post('Recover_pwd/set_pwd', 'Recover_pwd::set_pwd');
############## Fin Changement de mot de passe ########

#################### Debut Changer le mot de passe #################
$routes->get('Change_Password', 'Change_Password::index');
$routes->post('Change_Password/new_password', 'Change_Password::new_password');
#################### Fin Changer le mot de passe #################

############### module administration ##################
$routes->group('Administration', ['namespace' => 'App\Modules\Administration\Controllers'], function ($routes)
{
	$routes->post('Gestion_Utilisateurs/detail_instit','Gestion_Utilisateurs::detail_instit');
	$routes->post('Gestion_Utilisateurs/get_visualisation','Gestion_Utilisateurs::get_visualisation');

	$routes->post('Gestion_Utilisateurs/insert_tab1','Gestion_Utilisateurs::insert_tab1');
	$routes->post('Gestion_Utilisateurs/delete', 'Gestion_Utilisateurs::delete');
	$routes->post('Gestion_Utilisateurs/delete_affectation', 'Gestion_Utilisateurs::delete_affectation');
	
	$routes->get('Get_table_annee_budgetaire', 'Get_table_annee_budgetaire::index');
	$routes->match(['get', 'post'], 'User_profil/getVisualisation/(:any)', 'User_profil::getVisualisation/$1');
	$routes->match(['get', 'post'], 'User_profil/getDetail/(:any)', 'User_profil::getDetail/$1');
	$routes->post('Gestion_Utilisateurs/get_prof_niv','Gestion_Utilisateurs::get_prof_niv');
	$routes->get('Gestion_Utilisateurs', 'Gestion_Utilisateurs::index');
	$routes->post('Gestion_Utilisateurs/listing', 'Gestion_Utilisateurs::listing');
	$routes->post('Gestion_Utilisateurs/listing_cart', 'Gestion_Utilisateurs::listing_cart');
	$routes->get('Gestion_Utilisateurs/is_active/(:any)','Gestion_Utilisateurs::is_active/$1');
	$routes->get('Gestion_Utilisateurs/ajout','Gestion_Utilisateurs::ajout');
	$routes->post('Gestion_Utilisateurs/insert', 'Gestion_Utilisateurs::insert');
	$routes->get('Gestion_Utilisateurs/getOne/(:any)','Gestion_Utilisateurs::getOne/$1');
	$routes->post('Gestion_Utilisateurs/update','Gestion_Utilisateurs::update');
	$routes->post('Gestion_Utilisateurs/get_tutel','Gestion_Utilisateurs::get_tutel');
	$routes->post('Gestion_Utilisateurs/insert_tab_tempo', 'Gestion_Utilisateurs::insert_tab_tempo');
	$routes->post('Gestion_Utilisateurs/update_affectation', 'Gestion_Utilisateurs::update_affectation');
	// ---------------------------- Debut Gestion des utilisateurs -----------------------
	$routes->get('Gestion_Utilisateurs', 'Gestion_Utilisateurs::index');
	$routes->post('Gestion_Utilisateurs/listing', 'Gestion_Utilisateurs::listing');
	$routes->get('Gestion_Utilisateurs/is_active/(:any)','Gestion_Utilisateurs::is_active/$1');
	$routes->get('Gestion_Utilisateurs/ajout','Gestion_Utilisateurs::ajout');
	$routes->post('Gestion_Utilisateurs/insert', 'Gestiogn_Utilisateurs::insert');
	$routes->get('Gestion_Utilisateurs/getOne/(:any)','Gestion_Utilisateurs::getOne/$1');
	$routes->post('Gestion_Utilisateurs/update','Gestion_Utilisateurs::update');
	$routes->post('Gestion_Utilisateurs/get_tutel','Gestion_Utilisateurs::get_tutel');
	// ---------------------------- Fin Gestion des utilisateurs ----------------------

	// ---------------------------- Debut Gestion des profiles -----------------------
	$routes->get('User_profil','User_profil::index');
	$routes->post('User_profil/listing','User_profil::listing');
	$routes->get('User_profil/is_active/(:any)','User_profil::is_active/$1');
	$routes->get('User_profil/ajout','User_profil::ajout');
	$routes->post('User_profil/insert', 'User_profil::insert');
	$routes->get('User_profil/getOne/(:any)','User_profil::getOne/$1');
	$routes->post('User_profil/update','User_profil::update');
	// ---------------------------- Fin Gestion des profiles ----------------------
});
############### module administration ##################

############### Debut module dashboard ##################
$routes->group('dashboard', ['namespace' => 'App\Modules\dashboard\Controllers'], function ($routes)
{
	$routes->get('Dashboard_Comparaison_Budget/get_rapport', 'Dashboard_Comparaison_Budget::get_rapport');
	$routes->post('Dashboard_Comparaison_Budget/listing', 'Dashboard_Comparaison_Budget::listing');
	$routes->get('Dashboard_Comparaison_Budget/exporter/(:any)', 'Dashboard_Comparaison_Budget::exporter/$1');
	$routes->post('Dashboard_Grande_Masse/listing', 'Dashboard_Grande_Masse::listing');
	$routes->get('Dashboard_Grande_Masse/exporter/(:any)', 'Dashboard_Grande_Masse::exporter/$1');
	$routes->post('Proportion_allocation_institution/listing', 'Proportion_allocation_institution::listing');
	$routes->get('Proportion_allocation_institution/exporter/(:any)','Proportion_allocation_institution::exporter/$1');
	$routes->post('Dashboard_Performence_Excution/listing_dash_perform_exec', 'Dashboard_Performence_Excution::listing_dash_perform_exec');
	$routes->get('Dashboard_Performence_Excution/exporter/(:any)', 'Dashboard_Performence_Excution::exporter/$1');
	$routes->post('Dashbord_General_Ptba/listing_budget', 'Dashbord_General_Ptba::listing_budget');
	$routes->get('Dashbord_General_Ptba/exporter/(:any)', 'Dashbord_General_Ptba::exporter/$1');
	$routes->get('Dashboard_Taux_Phase_Vote/exporter/(:any)', 'Dashboard_Taux_Phase_Vote::exporter/$1');
	$routes->post('Dashbord_General_Execution/listing_execution', 'Dashbord_General_Execution::listing_execution');
	$routes->get('Dashbord_General_Execution/exporter/(:any)/(:any)/(:any)/(:any)/(:any)', 'Dashbord_General_Execution::exporter/$1/$2/$3/$4/$5');
	$routes->get('Dashbord_Suivi_Activite/exporter_activite_faire/(:any)', 'Dashbord_Suivi_Activite::exporter_activite_faire/$1');
	$routes->get('Dashbord_Suivi_Activite/exporter_activite_deja_fait/(:any)', 'Dashbord_Suivi_Activite::exporter_activite_deja_fait/$1');
	$routes->post('Dashboard_Transfert_budgetaire/listing', 'Dashboard_Transfert_budgetaire::listing');
	$routes->get('Dashboard_Transfert_budgetaire/exporter/(:any)', 'Dashboard_Transfert_budgetaire::exporter/$1');
	$routes->get('Dashboard_Depassement_Budget_Vote/exporter/(:any)','Dashboard_Depassement_Budget_Vote::exporter/$1');
	$routes->get('Dashboard_Exec_Phase_Comptable', 'Dashboard_Exec_Phase_Comptable::index');
	$routes->post('Dashboard_Exec_Phase_Comptable/get_rapport', 'Dashboard_Exec_Phase_Comptable::get_rapport');
	$routes->post('Dashboard_Exec_Phase_Comptable/detail_rapp', 'Dashboard_Exec_Phase_Comptable::detail_rapp');
	$routes->post('Dashboard_Exec_Phase_Comptable/detail_creance', 'Dashboard_Exec_Phase_Comptable::detail_creance');
	$routes->get('Rapport_Objectif_strategique/exporter/(:any)','Rapport_Objectif_strategique::exporter/$1');
	
	$routes->get('Rapport_Pip_piliers/exporter/(:any)','Rapport_Pip_piliers::exporter/$1');
	$routes->get('Rapport_Pip_Programme_Budgetaire', 'Rapport_Pip_Programme_Budgetaire::index');
	$routes->post('Rapport_Pip_Programme_Budgetaire/get_rapport', 'Rapport_Pip_Programme_Budgetaire::get_rapport');
	$routes->post('Rapport_Pip_Programme_Budgetaire/detail_inst', 'Rapport_Pip_Programme_Budgetaire::detail_inst');
	$routes->post('Rapport_Pip_Programme_Budgetaire/listing', 'Rapport_Pip_Programme_Budgetaire::listing');
	$routes->post('Rapport_Pip_Programme_Budgetaire/get_lieux', 'Rapport_Pip_Programme_Budgetaire::get_lieux');
	$routes->get('Rapport_Pip_Programme_Budgetaire/exporter/(:any)','Rapport_Pip_Programme_Budgetaire::exporter/$1');
	### pip par lieu d'intervention
	$routes->get('Rapport_Projet_localite', 'Rapport_Projet_localite::index');
	$routes->get('Rapport_Projet_localite/Get_localite', 'Rapport_Projet_localite::get_rapport');
	$routes->post('Rapport_Projet_localite/liste','Rapport_Projet_localite::liste');
	$routes->get('Rapport_Projet_localite/exporter/(:any)','Rapport_Projet_localite::exporter/$1');

	//Nombre des marchés par institution
	$routes->get('Rapport_Nbre_Marche_Inst', 'Rapport_Nbre_Marche_Inst::index');
	$routes->post('Rapport_Nbre_Marche_Inst/get_rapport', 'Rapport_Nbre_Marche_Inst::get_rapport');
	$routes->post('Rapport_Nbre_Marche_Inst/detail_nbre_marche', 'Rapport_Nbre_Marche_Inst::detail_nbre_marche');
	$routes->get('Rapport_Pip_Institution/exporter/(:any)','Rapport_Pip_Institution::exporter/$1');
	$routes->get('Rapport_Repartition_Projet_Statut/exporter/(:any)','Rapport_Repartition_Projet_Statut::exporter/$1');
	$routes->get('Rapport_Pip_Source_Financement/exporter/(:any)','Rapport_Pip_Source_Financement::exporter/$1');
	$routes->post('Rapport_Nbre_Marche_Inst/listing', 'Rapport_Nbre_Marche_Inst::listing');
	// Tableau de bord performance des raccrochages
	$routes->get('Dashbaord_Performance_Raccr', 'Dashbaord_Performance_Raccr::index');
	$routes->post('Dashbaord_Performance_Raccr/get_rapport', 'Dashbaord_Performance_Raccr::get_rapport');
	$routes->post('Dashbaord_Performance_Raccr/detail_nbre_projet', 'Dashbaord_Performance_Raccr::detail_nbre_projet');
	$routes->post('Dashbaord_Performance_Raccr/get_historique_traitement', 'Dashbaord_Performance_Raccr::get_historique_traitement');
	$routes->post('Dashbaord_Performance_Raccr/listing', 'Dashbaord_Performance_Raccr::listing');

	//fin

	$routes->get('Dashboard_Activite_Pasexecuter', 'Dashboard_Activite_Pasexecuter::index');
	$routes->post('Dashboard_Activite_Pasexecuter/get_activit', 'Dashboard_Activite_Pasexecuter::get_rapport');
	$routes->post('Dashboard_Activite_Pasexecuter/detail_activit','Dashboard_Activite_Pasexecuter::detail_activit');

	$routes->get('Dashboard_TCD_Engagement_Ordo', 'Dashboard_TCD_Engagement_Ordo::index');
	$routes->get('Dashboard_TCD_Engagement_Ordo/get_Performence_Excution_engage', 'Dashboard_TCD_Engagement_Ordo::get_rapport');
	$routes->post('Dashboard_TCD_Engagement_Ordo/detail_tcd_taux_engagement', 'Dashboard_TCD_Engagement_Ordo::detail_tcd_taux_engagements');
	$routes->post('Dashboard_TCD_Engagement_Ordo/liste_institution_phase_engages', 'Dashboard_TCD_Engagement_Ordo::liste_institution_phase_engage');

	$routes->get('Dashboard_Activite_Marche_Public', 'Dashboard_Activite_Marche_Public::index');
	$routes->post('Dashboard_Activite_Marche_Public/get_suivi_marche', 'Dashboard_Activite_Marche_Public::get_rapport');
	$routes->post('Dashboard_Activite_Marche_Public/listing', 'Dashboard_Activite_Marche_Public::liste_type_marche');
	
	$routes->post('Dashbord_General_PIP/detail_pip_etude', 'Dashbord_General_PIP::detail_pip_etudes');
	$routes->get('Rapport_Auteur_Pip_Etude', 'Rapport_Auteur_Pip_Etude::index');
	$routes->post('Rapport_Auteur_Pip_Etude/get_rapport_etude', 'Rapport_Auteur_Pip_Etude::get_rapport');
	$routes->post('Rapport_Auteur_Pip_Etude/listing', 'Rapport_Auteur_Pip_Etude::listing');
	#### process execution budgetaire admin 2
	$routes->get('Exec_budgetaire_Admin2', 'Exec_budgetaire_Admin2::index');
	$routes->get('Exec_budgetaire_Admin2/Get_Exec_Admin2', 'Exec_budgetaire_Admin2::get_rapport');
	$routes->post('Exec_budgetaire_Admin2/detail_admin2', 'Exec_budgetaire_Admin2::detail_admin2');
	$routes->post('Exec_budgetaire_Admin2/detail_liquid', 'Exec_budgetaire_Admin2::detail_liquid');
	$routes->post('Exec_budgetaire_Admin2/detail_rejet_valid', 'Exec_budgetaire_Admin2::detail_rejet_valid');
	$routes->post('Exec_budgetaire_Admin2/detail2', 'Exec_budgetaire_Admin2::detail2');
	$routes->post('Dashboard_Suivi_budget/listing', 'Dashboard_Suivi_budget::listing');

	$routes->get('Dashboard_PIP_Annee_Budgetaire', 'Dashboard_PIP_Annee_Budgetaire::index');
	$routes->post('Dashboard_PIP_Annee_Budgetaire/get_annee_budget', 'Dashboard_PIP_Annee_Budgetaire::get_rapport');
	$routes->post('Dashboard_PIP_Annee_Budgetaire/detail_source', 'Dashboard_PIP_Annee_Budgetaire::detail_source');
	$routes->post('Dashboard_PIP_Annee_Budgetaire/detail_source_montant', 'Dashboard_PIP_Annee_Budgetaire::detail_source_montant');

	//Tableau de bord General des PIP par Institution et par anée budgétaire par claude
	$routes->get('Dashboard_Pip_General_Annee', 'Dashboard_Pip_General_Annee::index');
	$routes->post('Dashboard_Pip_General_Annee/get_rapport', 'Dashboard_Pip_General_Annee::get_rapport');
	$routes->post('detail_projet_annee', 'Dashboard_Pip_General_Annee::detail_projet_annee');

	$routes->post('Dashboard_Pip_General_Annee/listing', 'Dashboard_Pip_General_Annee::listing');
	### planification nationale
	$routes->get('Dashboard_Planification_nationale', 'Dashboard_Planification_nationale::index');
	$routes->get('Dashboard_Planification_nationale/Get_Planif', 'Dashboard_Planification_nationale::get_rapport');
	$routes->post('Dashboard_Planification_nationale/detail_costab','Dashboard_Planification_nationale::detail_costab');
	$routes->post('Dashboard_Planification_nationale/detail_clmr','Dashboard_Planification_nationale::detail_clmr');
	
	## Debut Rapport Dashbord_Suivi_Activite
	$routes->get('Dashbord_Suivi_Activite', 'Dashbord_Suivi_Activite::index');
	$routes->post('Dashbord_Suivi_Activite/get_rapport', 'Dashbord_Suivi_Activite::get_rapport');
	$routes->post('Dashbord_Suivi_Activite/listing', 'Dashbord_Suivi_Activite::listing');

	$routes->post('Dashbord_Suivi_Activite/listing_deux', 'Dashbord_Suivi_Activite::listing_deux');
	$routes->post('Dashbord_Suivi_Activite/detail_suivi', 'Dashbord_Suivi_Activite::detail_suivi');
	## Fin Dashbord_Suivi_Activite

	### Suivi du budget
	$routes->get('Dashboard_Suivi_budget', 'Dashboard_Suivi_budget::index');
	$routes->get('Dashboard_Suivi_budget/get_suivi_bugdet', 'Dashboard_Suivi_budget::get_rapport');
	$routes->post('Dashboard_Suivi_budget/detail_suivi_bugdet','Dashboard_Suivi_budget::detail_suivi_bugdet');

	$routes->get('Rapport_Couts', 'Rapport_Couts::index');
	$routes->get('Rapport_Couts/Get_Cout', 'Rapport_Couts::get_rapport');
	$routes->post('Rapport_Couts/detail_cout','Rapport_Couts::detail_cout');
	$routes->post('Rapport_Couts/liste','Rapport_Couts::liste');

	#Tableau 13: Gaps de financement par ministère et institution (en BIF)
	$routes->get('Rapport_Gap_Finance_Institution', 'Rapport_Gap_Finance_Institution::index');
	$routes->post('Rapport_Gap_Finance_Institution/get_rapport', 'Rapport_Gap_Finance_Institution::get_rapport');
	$routes->post('Rapport_Gap_Finance_Institution/detail_gap', 'Rapport_Gap_Finance_Institution::detail_gap');

	$routes->post('Rapport_Gap_Finance_Institution/listing', 'Rapport_Gap_Finance_Institution::listing');
	$routes->post('Rapport_Gap_Finance_Institution/get_lieux', 'Rapport_Gap_Finance_Institution::get_lieux');

	//Nombre de projets par Institution 

	$routes->get('Rapport_Nbre_Projet_Inst', 'Rapport_Nbre_Projet_Inst::index');
	$routes->post('Rapport_Nbre_Projet_Inst/get_rapport', 'Rapport_Nbre_Projet_Inst::get_rapport');
	$routes->post('Rapport_Nbre_Projet_Inst/detail_nbre_projet', 'Rapport_Nbre_Projet_Inst::detail_nbre_projet');

	$routes->post('Rapport_Nbre_Projet_Inst/listing', 'Rapport_Nbre_Projet_Inst::listing');

  	#Tableau 14: Répartition du budget national par grande rubrique budgétaire par Ministère et Institution pour la période 2023-2024/2025-26 (en BIF)
	$routes->get('Rapport_Budget_National', 'Rapport_Budget_National::index');
	$routes->post('Rapport_Budget_National/get_rapport', 'Rapport_Budget_National::get_rapport');
	$routes->post('Rapport_Budget_National/detail_rubrique', 'Rapport_Budget_National::detail_rubrique');
	$routes->post('Rapport_Budget_National/listing', 'Rapport_Budget_National::listing');
	$routes->post('Rapport_Budget_National/get_lieux', 'Rapport_Budget_National::get_lieux');
	//Début dashboard PIP
	$routes->get('Dashbord_General_PIP', 'Dashbord_General_PIP::index');
	$routes->get('Dashbord_General_PIP/get_general_pip', 'Dashbord_General_PIP::get_rapport');
	$routes->post('Dashbord_General_PIP/detail_pip_pilier', 'Dashbord_General_PIP::detail_pip_piliers');
	$routes->post('Dashbord_General_PIP/detail_pip_axe', 'Dashbord_General_PIP::detail_pip_axes');
	$routes->post('Dashbord_General_PIP/detail_pip_bailleur', 'Dashbord_General_PIP::detail_pip_bailleurs');

	$routes->post('Dashbord_General_PIP/detail_pip_statut_projet', 'Dashbord_General_PIP::detail_pip_statut_projets');

	$routes->post('Dashbord_General_PIP/detail_pip_secteur', 'Dashbord_General_PIP::detail_pip_secteurs');

	$routes->post('Dashbord_General_PIP/detail_pip_lieu_intervention', 'Dashbord_General_PIP::detail_pip_lieu_interventions');
	$routes->get('Rapport_Pip_piliers', 'Rapport_Pip_piliers::index');
	$routes->get('Rapport_Pip_piliers/Get_Pillier', 'Rapport_Pip_piliers::get_rapport');
	$routes->post('Rapport_Pip_piliers/detail_pil','Rapport_Pip_piliers::detail_pil');
	$routes->post('Rapport_Pip_piliers/liste','Rapport_Pip_piliers::liste');


	$routes->get('Rapport_Repartition_Projet_Statut', 'Rapport_Repartition_Projet_Statut::index');
	$routes->post('Rapport_Repartition_Projet_Statut/get_rapport', 'Rapport_Repartition_Projet_Statut::get_rapport');
	$routes->post('Rapport_Repartition_Projet_Statut/detail_statut', 'Rapport_Repartition_Projet_Statut::detail_statut');

	$routes->post('Rapport_Repartition_Projet_Statut/listing', 'Rapport_Repartition_Projet_Statut::listing');

	$routes->post('Rapport_Repartition_Projet_Statut/get_lieux', 'Rapport_Repartition_Projet_Statut::get_lieux');



	### pip selon les Objectifs Stratégiques
	$routes->get('Rapport_Objectif_strategique', 'Rapport_Objectif_strategique::index');
	$routes->get('Rapport_Objectif_strategique/Get_Strategique', 'Rapport_Objectif_strategique::get_rapport');
	$routes->post('Rapport_Objectif_strategique/detail_strategique','Rapport_Objectif_strategique::detail_strategique');
	$routes->post('Rapport_Objectif_strategique/liste','Rapport_Objectif_strategique::liste');

	### pip selon les Objectifs Stratégiques du PND
	$routes->get('Rapport_Strategique_pnd', 'Rapport_Strategique_pnd::index');
	$routes->get('Rapport_Strategique_pnd/Get_Strategique_pnd', 'Rapport_Strategique_pnd::get_rapport');
	$routes->post('Rapport_Strategique_pnd/detail_strategique_pnd','Rapport_Strategique_pnd::detail_strategique_pnd');
	$routes->post('Rapport_Strategique_pnd/liste','Rapport_Strategique_pnd::liste');


	#Répartition de l\'Investissement public source de Financement 

	$routes->get('Rapport_Pip_Source_Financement', 'Rapport_Pip_Source_Financement::index');
	$routes->post('Rapport_Pip_Source_Financement/get_rapport', 'Rapport_Pip_Source_Financement::get_rapport');
	$routes->post('Rapport_Pip_Source_Financement/detail_source', 'Rapport_Pip_Source_Financement::detail_source');

	$routes->post('Rapport_Pip_Source_Financement/listing', 'Rapport_Pip_Source_Financement::listing');

	$routes->post('Rapport_Pip_Source_Financement/get_lieux', 'Rapport_Pip_Source_Financement::get_lieux');

	#Répartition de l\'Investissement public par pilier 

	$routes->get('Rapport_Pip_Pilier', 'Rapport_Pip_Pilier::index');
	$routes->post('Rapport_Pip_Pilier/get_rapport', 'Rapport_Pip_Pilier::get_rapport');
	$routes->post('Rapport_Pip_Pilier/detail_pilier_pip', 'Rapport_Pip_Pilier::detail_pilier_pip');

	$routes->post('Rapport_Pip_Pilier/listing', 'Rapport_Pip_Pilier::listing');

	$routes->post('Rapport_Pip_Pilier/get_lieux', 'Rapport_Pip_Pilier::get_lieux');


	## Debut Rapport Répartition des projet par intervention
	$routes->get('Rapport_Projet_Intervention', 'Rapport_Projet_Intervention::index');
	$routes->post('Rapport_Projet_Intervention/get_rapport', 'Rapport_Projet_Intervention::get_rapport');
	$routes->post('Rapport_Projet_Intervention/detail_intervention', 'Rapport_Projet_Intervention::detail_intervention');

	$routes->post('Rapport_Projet_Intervention/listing', 'Rapport_Projet_Intervention::listing');

	$routes->post('Rapport_Projet_Intervention/get_lieux', 'Rapport_Projet_Intervention::get_lieux');
	

	## Répartition de l\'Investissement public par ministère et institution
	$routes->get('Rapport_Pip_Institution', 'Rapport_Pip_Institution::index');
	$routes->post('Rapport_Pip_Institution/get_rapport', 'Rapport_Pip_Institution::get_rapport');
	$routes->post('Rapport_Pip_Institution/detail_inst', 'Rapport_Pip_Institution::detail_inst');

	$routes->post('Rapport_Pip_Institution/listing', 'Rapport_Pip_Institution::listing');

	$routes->post('Rapport_Pip_Institution/get_lieux', 'Rapport_Pip_Institution::get_lieux');

    //fin dashboard PIP
	$routes->get('Dashboard_Performance_Enqueteur', 'Dashboard_Performance_Enqueteur::index');
	$routes->get('Dashboard_Performance_Enqueteur/get_top_enqueteurs_engagements', 'Dashboard_Performance_Enqueteur::get_rapport');
	$routes->post('Dashboard_Performance_Enqueteur/detail_top_performance', 'Dashboard_Performance_Enqueteur::detail_top_performances');

	$routes->post('Dashboard_Performance_Enqueteur/liste_acteurs_performance_vote', 'Dashboard_Performance_Enqueteur::liste_acteurs_performance_votes');

	$routes->get('Dashbord_Ex_Phase_Administrative1', 'Dashbord_Ex_Phase_Administrative1::index');

	$routes->get('Dashbord_Ex_Phase_Administrative1/execution_phase_administrative1', 'Dashbord_Ex_Phase_Administrative1::get_rapport');

	$routes->post('Dashbord_Ex_Phase_Administrative1/detail_execution_phase_administrative1', 'Dashbord_Ex_Phase_Administrative1::detail_execution_phase_administratives1');
	$routes->post('Dashbord_Ex_Phase_Administrative1/liste_execution_phase_administratives', 'Dashbord_Ex_Phase_Administrative1::liste_execution_phase_administratives1');

	$routes->get('Dashboard_Depassement_Budget_Vote', 'Dashboard_Depassement_Budget_Vote::index');
	$routes->get('Dashboard_Depassement_Budget_Vote/execution_deppasement_budget_vote', 'Dashboard_Depassement_Budget_Vote::get_rapport');
	$routes->post('Dashboard_Depassement_Budget_Vote/detail_depassement_budget_vote', 'Dashboard_Depassement_Budget_Vote::detail_depassement_budget_votes');
	$routes->post('Dashboard_Depassement_Budget_Vote/liste_depassement_budget_vote', 'Dashboard_Depassement_Budget_Vote::liste_depassement_budget_votes');

	$routes->post('Dashboard_Depassement_Budget_Vote/liste_activites/(:any)','Dashboard_Depassement_Budget_Vote::liste_activites/$1');

	$routes->get('Dashboard_TCD_Valeur_DEP', 'Dashboard_TCD_Valeur_DEP::index');
	$routes->get('Dashboard_TCD_Valeur_DEP/get_Performence_Excution_Vote', 'Dashboard_TCD_Valeur_DEP::get_rapport');
	$routes->post('Dashboard_TCD_Valeur_DEP/get_Performence_Liste_Execution', 'Dashboard_TCD_Valeur_DEP::Liste_institution_phase');
	$routes->post('Dashboard_TCD_Valeur_DEP/detail_perfo_vote_execution', 'Dashboard_TCD_Valeur_DEP::detail_perfo_vote_executions');
	$routes->get('Dashboard_Taux_Phase_Engagement', 'Dashboard_Taux_Phase_Engagement::index');
	$routes->get('Dashboard_Taux_Phase_Engagement/execution_Croise_dynamique', 'Dashboard_Taux_Phase_Engagement::get_rapport');
	$routes->post('Dashboard_Taux_Phase_Engagement/detail_croise_dynamique', 'Dashboard_Taux_Phase_Engagement::detail_croise_dynamiques');
	$routes->post('Dashboard_Taux_Phase_Engagement/liste_institution_croise_dynamiques', 'Dashboard_Taux_Phase_Engagement::liste_institution_croise_dynamique');
	$routes->get('Dashboard_TCD_Taux_Engagement', 'Dashboard_TCD_Taux_Engagement::index');
	$routes->get('Dashboard_TCD_Taux_Engagement/get_Performence_Excution_engage', 'Dashboard_TCD_Taux_Engagement::get_rapport');
	$routes->post('Dashboard_TCD_Taux_Engagement/detail_tcd_taux_engagement', 'Dashboard_TCD_Taux_Engagement::detail_tcd_taux_engagements');
	$routes->post('Dashboard_TCD_Taux_Engagement/liste_institution_phase_engages', 'Dashboard_TCD_Taux_Engagement::liste_institution_phase_engage');
	$routes->get('Dashboard_Taux_Phase_Vote', 'Dashboard_Taux_Phase_Vote::index');
	$routes->get('Dashboard_Taux_Phase_Vote/get_taux_phase_vote', 'Dashboard_Taux_Phase_Vote::get_rapport');
	$routes->post('Dashboard_Taux_Phase_Vote/detail_tcd_taux_vote', 'Dashboard_Taux_Phase_Vote::detail_tcd_taux_votes');
	$routes->post('Dashboard_Taux_Phase_Vote/liste_institution_taux_votes', 'Dashboard_Taux_Phase_Vote::liste_institution_taux_votes');
	$routes->get('Dashboard_TCD_Valeur_Engagement_Vote', 'Dashboard_TCD_Valeur_Engagement_Vote::index');
	$routes->get('Dashboard_TCD_Valeur_Engagement_Vote/get_Excution_engage_vote', 'Dashboard_TCD_Valeur_Engagement_Vote::get_rapport');
	$routes->post('Dashboard_TCD_Valeur_Engagement_Vote/detail_tcd_engagement_vote', 'Dashboard_TCD_Valeur_Engagement_Vote::detail_tcd_engagement_votes');
	$routes->post('Dashboard_TCD_Valeur_Engagement_Vote/liste_institution_engage_vote', 'Dashboard_TCD_Valeur_Engagement_Vote::liste_institution_engage_votes');
	$routes->get('Dashboard_TCD_Valeur_Engagement_Vote/exporter/(:any)','Dashboard_TCD_Valeur_Engagement_Vote::exporter/$1');
	$routes->get('Dashboard_Valeur_Phase_Engagement', 'Dashboard_Valeur_Phase_Engagement::index');
	$routes->get('Dashboard_Valeur_Phase_Engagement/execution_valeur_dynamique', 'Dashboard_Valeur_Phase_Engagement::get_rapport');
	$routes->post('Dashboard_Valeur_Phase_Engagement/detail_valeur_dynamique', 'Dashboard_Valeur_Phase_Engagement::detail_valeur_dynamiques');
	$routes->post('Dashboard_Valeur_Phase_Engagement/liste_institution_valeur_dynamiques', 'Dashboard_Valeur_Phase_Engagement::liste_institution_valeur_dynamique');
	$routes->post('Dashbord_Taux_Execution_Circuit_Demande/detail_circuit_Excution_demande', 'Dashbord_Taux_Execution_Circuit_Demande::detail_circuit_Excution_demandes');
	$routes->get('Dashbord_Taux_Execution_Circuit_Demande', 'Dashbord_Taux_Execution_Circuit_Demande::index');
	$routes->get('Dashbord_Taux_Execution_Circuit_Demande/execution_Circuit_demande', 'Dashbord_Taux_Execution_Circuit_Demande::get_rapport');
	$routes->post('Dashbord_Taux_Execution_Circuit_Demande/detail_budget_annulation', 'Dashbord_Taux_Execution_Circuit_Demande::detail_budget_annulations');
	$routes->get('Dashbord_Budget_Annulation', 'Dashbord_Budget_Annulation::index');
	$routes->get('Dashbord_Budget_Annulation/get_annulation_Rapport', 'Dashbord_Budget_Annulation::get_rapport');
	$routes->post('Dashbord_Budget_Annulation/detail_budget_annulation', 'Dashbord_Budget_Annulation::detail_budget_annulations');
	$routes->get('Dashboard_Suivi_TD_Etape', 'Dashboard_Suivi_TD_Etape::index');
	$routes->get('Dashboard_Suivi_TD_Etape/get_suivi_double_etape', 'Dashboard_Suivi_TD_Etape::get_rapport');
	$routes->post('Dashboard_Suivi_TD_Etape/detail_td_etape', 'Dashboard_Suivi_TD_Etape::detail_tds_etapes');

	$routes->get('Dashboard_Valeur_Phase_Engagement/exporter/(:any)','Dashboard_Valeur_Phase_Engagement::exporter/$1');
	$routes->get('Dashboard_TCD_Taux_Engagement/exporter/(:any)','Dashboard_TCD_Taux_Engagement::exporter/$1');
	$routes->get('Dashboard_Taux_Phase_Engagement/exporter/(:any)','Dashboard_Taux_Phase_Engagement::exporter/$1');
	// Debut double commande
	$routes->get('Dashboard_Suivi_Double_Commande', 'Dashboard_Suivi_Double_Commande::index');
	$routes->get('Dashboard_Suivi_Double_Commande/get_suivi_double_commande', 'Dashboard_Suivi_Double_Commande::get_rapport');
	$routes->post('Dashboard_Suivi_Double_Commande/detail_classements_fonctionnels', 'Dashboard_Suivi_Double_Commande::detail_classements_fonctionnelss');

	$routes->post('Dashboard_Suivi_Double_Commande/detail_executions_mouvements', 'Dashboard_Suivi_Double_Commande::detail_executions_mouvementss');
	// Fin double commande
	$routes->post('Dashboard_Performance_Decrochage/detail_racrochage_ministere', 'Dashboard_Performance_Decrochage::detail_racrochage_ministeres');
	$routes->get('Evolution_Raccrochage', 'Evolution_Raccrochage::index');
	$routes->get('Evolution_Raccrochage/get_evolution_raccrochage', 'Evolution_Raccrochage::get_rapport');
	$routes->get('Dashboard_Performance_Decrochage', 'Dashboard_Performance_Decrochage::index');

	$routes->get('Dashboard_Performance_Decrochage/get_performance_raccrochage', 'Dashboard_Performance_Decrochage::get_rapport');

	$routes->get('Dashboard_Transfert_budgetaire', 'Dashboard_Transfert_budgetaire::index');
	$routes->get('Dashboard_Transfert_budgetaire/get_transfert_budgetaire', 'Dashboard_Transfert_budgetaire::get_rapport');
	$routes->post('Dashboard_Transfert_budgetaire/detail_transfert_budgetaire', 'Dashboard_Transfert_budgetaire::detail_transfert_budgetaires');
	$routes->get('Rapport_Execution_Budgetaire', 'Rapport_Execution_Budgetaire::index');
	$routes->get('Rapport_Execution_Budgetaire/get_general_execution_budgetaire', 'Rapport_Execution_Budgetaire::get_rapport');
	$routes->post('Rapport_Execution_Budgetaire/detail_execution_budgetaire', 'Rapport_Execution_Budgetaire::detail_execution_budgetaires');
	########## debut dashboard  comparaison########
	$routes->get('Dashboard_Comparaison_Budget', 'Dashboard_Comparaison_Budget::index');
	
	$routes->post('Dashboard_Comparaison_Budget/detail_Comparaison_Budget', 'Dashboard_Comparaison_Budget::detail_Comparaison_Budget');
	########## fin dashboard  comparaison########
	$routes->get('Dashbord_General_Execution', 'Dashbord_General_Execution::index');
	$routes->get('Dashbord_General_Execution/get_general_execution_Rapport', 'Dashbord_General_Execution::get_rapport');
	$routes->post('Dashbord_General_Execution/detail_general_execution', 'Dashbord_General_Execution::detail_general_executions');
	$routes->post('Dashbord_General_Execution/detail_execution_Gdemasse', 'Dashbord_General_Execution::detail_execution_Gdemasses');
	$routes->post('Dashbord_General_Execution/detail_execution_mouvement', 'Dashbord_General_Execution::detail_execution_mouvements');
	$routes->get('Dashbord_Acrochage_Activite', 'Dashbord_Acrochage_Activite::index');
	$routes->get('Dashbord_Acrochage_Activite/get_acrochage_activi_Rapport', 'Dashbord_Acrochage_Activite::get_rapport');
	$routes->post('Dashbord_Acrochage_Activite/detail_raccrochage_activite','Dashbord_Acrochage_Activite::detail_raccrochages_activite');
	$routes->post('Dashbord_Acrochage_Activite/detail_raccrochage_Gdemasse','Dashbord_Acrochage_Activite::detail_raccrochage_Gdemasses');
	## Rapport comparaison montant vote vs plafond @claude
	$routes->get('Comparaison_Vote_Plafond', 'Comparaison_Vote_Plafond::index');
	$routes->post('Comparaison_Vote_Plafond/get_rapport', 'Comparaison_Vote_Plafond::get_rapport');

	$routes->post('Comparaison_Vote_Plafond/detail_compa', 'Comparaison_Vote_Plafond::detail_compa');

	####################rapport budget exécuté begin##################
	$routes->get('Budget_Execute_Institution', 'Budget_Execute_Institution::index');
	$routes->get('Budget_Execute_Institution/get_Rapport', 'Budget_Execute_Institution::get_Rapport');
	$routes->post('Budget_Execute_Institution/detail_budget_execute', 'Budget_Execute_Institution::detail_budget_execute');
	####################rapport budget exécuté end##################
	##########dashboard  anomalies########
	$routes->get('Dashboard_Des_Anomalies', 'Dashboard_Des_Anomalies::index');
	$routes->get('Dashboard_Des_Anomalies/Get_anomalies', 'Dashboard_Des_Anomalies::get_rapport');
	$routes->post('Dashboard_Des_Anomalies/detail_anomalie', 'Dashboard_Des_Anomalies::detail_anomalie');
	##########dashboard  realisation par mouvement########
	$routes->get('Dashboard_Excution_Par_Etape', 'Dashboard_Excution_Par_Etape::index');
	$routes->get('Dashboard_Excution_Par_Etape/Get_Excution_Par_Etape', 'Dashboard_Excution_Par_Etape::get_rapport');
	$routes->post('Dashboard_Excution_Par_Etape/detail_perfo', 'Dashboard_Excution_Par_Etape::detail_perfo');
	##########dashboard  realisation par mouvement########
	$routes->post('Dashbord_General_Ptba/detail_general_activite_vote','Dashbord_General_Ptba::detail_generals_activite_vote');
	
	####################tableau de bord des grandes masses begin##################
	$routes->get('Dashboard_Grande_Masse', 'Dashboard_Grande_Masse::index');
	$routes->get('Get_Dashboard_Grande_Masse', 'Dashboard_Grande_Masse::get_rapport');
	$routes->post('Dashboard_Grande_Masse/detail_GMV', 'Dashboard_Grande_Masse::detail_GMV');
	$routes->post('Dashboard_Grande_Masse/detail_GMR', 'Dashboard_Grande_Masse::detail_GMR');
	$routes->post('Dashboard_Grande_Masse/detail_GME', 'Dashboard_Grande_Masse::detail_GME');
	####################tableau de bord des grandes masses end##################
	$routes->get('Rapport_Class_economique','Rapport_Class_economique::index');
	$routes->get('Rapport_Class_economique/get_Eco','Rapport_Class_economique::get_rapport'); 
	$routes->post('Rapport_Class_economique/detail_','Rapport_Class_economique::detail_');
	$routes->get('Class_fonctionnel','Class_fonctionnel::index');
	$routes->get('Class_fonctionnel/get_fonction','Class_fonctionnel::get_rapport'); 
	$routes->post('Class_fonctionnel/detail','Class_fonctionnel::detail');
	##### vote gde masse
	$routes->get('Budget_Vote_Grandemasse','Budget_Vote_Grandemasse::index');
	$routes->get('Budget_Vote_Grandemasse/get_GdeMasse','Budget_Vote_Grandemasse::get_rapport'); 
	$routes->post('Budget_Vote_Grandemasse/detail_Gdemasse','Budget_Vote_Grandemasse::detail_Gdemasse');
	$routes->post('Budget_Vote_Grandemasse/detail_tranche','Budget_Vote_Grandemasse::detail_tranche');

	$routes->post('Dashboard_Realisation_Non/detail_realisation_non', 'Dashboard_Realisation_Non::detail_realisations_non');
	$routes->post('Dashboard_Realisation_Non/detail_realisation_institution','Dashboard_Realisation_Non::detail_realisations_institution');
	##########dashboard des taux_excution########
	$routes->get('Dashboard_Taux_Realisation', 'Dashboard_Taux_Realisation::index');
	$routes->get('Dashboard_Taux_Realisation/get_Taux_Realisation', 'Dashboard_Taux_Realisation::get_rapport');
	$routes->post('Dashboard_Taux_Realisation/detail_get_Taux_Realisation','Dashboard_Taux_Realisation::detail_get_Taux_Realisation');
	## Debut Rapport classification administrative
	$routes->get('Classification_Admin', 'Classification_Admin::index');
	$routes->post('Classification_Admin/get_rapport', 'Classification_Admin::get_rapport');
	$routes->post('Classification_Admin/detail', 'Classification_Admin::detail');
	## Fin Rapport classification administrative
	## Rapport allocation du budget par institution @claude
	$routes->get('Proportion_allocation_institution', 'Proportion_allocation_institution::index');
	$routes->post('Proportion_allocation_institution/get_rapport', 'Proportion_allocation_institution::get_rapport');
	$routes->post('Proportion_allocation_institution/detail_proportion', 'Proportion_allocation_institution::detail_proportion');
	#################### grande masse vote begin##################
	$routes->get('Grande_Masse_Vote', 'Grande_Masse_Vote::index');
	$routes->post('TB_Grande_Masse_Vote', 'Grande_Masse_Vote::get_rapport');
	$routes->post('Grande_Masse_Vote/detail_GM', 'Grande_Masse_Vote::detail_GM');
	#################### grande masse vote  end##################

	##########dashboard des performence_excution########
	$routes->get('Dashboard_Performence_Excution', 'Dashboard_Performence_Excution::index');
	$routes->get('Dashboard_Performence_Excution/get_Performence_Excution','Dashboard_Performence_Excution::get_rapport');
	$routes->post('Dashboard_Performence_Excution/detail_Performence_Excution', 'Dashboard_Performence_Excution::detail_Performence_Excution');

  	##########dashboard  bugtaire actuel########
	$routes->get('Dashboard_Bugdet_Exc_Actuel', 'Dashboard_Bugdet_Exc_Actuel::index');
	$routes->get('Dashboard_Bugdet_Exc_Actuel/get_actuel', 'Dashboard_Bugdet_Exc_Actuel::get_rapport');
	$routes->post('Dashboard_Bugdet_Exc_Actuel/detail_actuel', 'Dashboard_Bugdet_Exc_Actuel::detail_actuel');
	$routes->post('Dashbord_General_Ptba/detail_ptba_Gdemasse', 'Dashbord_General_Ptba::detail_ptba_Gdemasses');
	$routes->post('Dashbord_General_Ptba/detail_ptba_transfert', 'Dashbord_General_Ptba::detail_ptba_transferts');
	$routes->post('Dashbord_General_Ptba/detail_ptba_recu', 'Dashbord_General_Ptba::detail_ptba_recus');
	######route emery
	$routes->get('Dashboard_Comparaison_Vote_Execution', 'Dashboard_Comparaison_Vote_Execution::index');
	$routes->get('Dashboard_Comparaison_Vote_Execution/get_comparaison_vote', 'Dashboard_Comparaison_Vote_Execution::get_rapport');
	$routes->post('Dashboard_Comparaison_Vote_Execution/detail_comparaison_vote', 'Dashboard_Comparaison_Vote_Execution::detail_comparaisons_vote');
	$routes->post('detail_comparaison_execution', 'Dashboard_Comparaison_Vote_Execution::detail_comparaisons_execution');
	$routes->post('Dashboard_Comparaison_Vote_Execution/detail_phase_budget', 'Dashboard_Comparaison_Vote_Execution::detail_phases_budget');
	$routes->get('Dashbord_General_Ptba', 'Dashbord_General_Ptba::index');
	$routes->get('Dashbord_General_Ptba/get_general_Rapport', 'Dashbord_General_Ptba::get_rapport');
	$routes->post('Dashbord_General_Ptba/detail_general_vote', 'Dashbord_General_Ptba::detail_generals_vote');
	$routes->post('Dashbord_General_Ptba/detail_general_phase', 'Dashbord_General_Ptba::detail_generals_phase');
	$routes->get('Dashboard_Exection_Budget_Phase', 'Dashboard_Exection_Budget_Phase::index');
	$routes->get('Dashboard_Exection_Budget_Phase/get_phase_budget', 'Dashboard_Exection_Budget_Phase::get_rapport');
	$routes->post('Dashboard_Exection_Budget_Phase/detail_phase_budget', 'Dashboard_Exection_Budget_Phase::detail_phase_budget');
  	###### route emery fin
	$routes->Add('Suivi_Rapport_Evaluation', 'Suivi_Rapport_Evaluation::index');
	$routes->post('Suivi_Rapport_Evaluation/liste', 'Suivi_Rapport_Evaluation::liste');
	$routes->get('Suivi_Rapport_Evaluation/get_programme/(:any)', 'Suivi_Rapport_Evaluation::get_programme/$1');
	$routes->get('Suivi_Rapport_Evaluation/get_sous_tutelle/(:any)', 'Suivi_Rapport_Evaluation::get_sous_tutelle/$1');
	$routes->get('Suivi_Rapport_Evaluation/get_action/(:any)', 'Suivi_Rapport_Evaluation::get_action/$1');
	$routes->get('Suivi_Rapport_Evaluation/exporter', 'Suivi_Rapport_Evaluation::exporter');
  	########## dashboard des institution_ programme########
	$routes->get('Dashboard_Vote_Inst_Progr', 'Dashboard_Vote_Inst_Progr::index');
	$routes->get('Dashboard_Vote_Inst_Progr/get_Inst_Progr', 'Dashboard_Vote_Inst_Progr::get_rapport');
	$routes->post('Dashboard_Vote_Inst_Progr/detail_Inst_Progr', 'Dashboard_Vote_Inst_Progr::detail_Inst_Progr');
	########## dashboard des ligne bugtaire########
	$routes->get('Dashboard_Bugdet_Vote_Lb', 'Dashboard_Bugdet_Vote_Lb::index');
	$routes->get('Dashboard_Bugdet_Vote_Lb/detail_Lb_Rapport', 'Dashboard_Bugdet_Vote_Lb::get_rapport');
	$routes->post('Dashboard_Bugdet_Vote_Lb/detail_Lb', 'Dashboard_Bugdet_Vote_Lb::detail_Lb');
	$routes->post('Dashboard_Bugdet_Vote_Lb/detail_Lb_diff', 'Dashboard_Bugdet_Vote_Lb::detail_Lb_diff');
	########## dashboard des institutions########
	$routes->get('Dashboard_Budget_vote', 'Dashboard_Budget_vote::index');
	$routes->get('Dashboard_Budget_vote/get_bugdet', 'Dashboard_Budget_vote::get_rapport');
	$routes->post('Dashboard_Budget_vote/detail_ministere', 'Dashboard_Budget_vote::detail_ministere');
	########## dashboard des institutions########

	#### 
	$routes->get('Budget_transfer', 'Budget_transfer::index');
	$routes->get('Budget_transfer/get_transfer', 'Budget_transfer::get_rapport');
	$routes->post('Budget_transfer/detail_transfer', 'Budget_transfer::detail_transfer');

  	#### 
	$routes->get('Budget_Exec_Gdemasse', 'Budget_Exec_Gdemasse::index');
	$routes->get('Budget_Exec_Gdemasse/get_masse', 'Budget_Exec_Gdemasse::get_rapport');
	$routes->post('Budget_Exec_Gdemasse/detail_masse', 'Budget_Exec_Gdemasse::detail_masse');

	##########
	$routes->post('Dashboard_Comparaison_Vote_Execution/detail_phase_budget', 'Dashboard_Comparaison_Vote_Execution::detail_phases_budget');
	$routes->get('Dashboard_Suivi_Evaluation', 'Dashboard_Suivi_Evaluation::index');
	$routes->get('Dashboard_Suivi_Evaluation/get_suivi_evaluation_budget', 'Dashboard_Suivi_Evaluation::get_rapport');

	$routes->get('Dashboard_Suivi_Evaluation/get_suivi_evaluation_budget', 'Dashboard_Suivi_Evaluation::get_rapport');

	$routes->post('Dashboard_Suivi_Evaluation/detail_classement_fonctionnel', 'Dashboard_Suivi_Evaluation::detail_classement_fonctionnels');
	$routes->post('Dashboard_Suivi_Evaluation/detail_classement_fonctionnels', 'Dashboard_Suivi_Evaluation::detail_classement_fonctionnelss');

	$routes->get('Dashboard_Realisation_Non', 'Dashboard_Realisation_Non::index');
	$routes->get('Dashboard_Realisation_Non/get_realisation_Rapport', 'Dashboard_Realisation_Non::get_rapport');
});
############### Fin module dashboard ####################

############### Debut module donnees ##################
$routes->group('donnees', ['namespace' => 'App\Modules\donnees\Controllers'], function ($routes)
{
	// ----------------------- Debut Lignes Budgétaires nouveau format--------------------------------------
	$routes->get('Import_Ligne_Budgetaire','Import_Ligne_Budgetaire::index');
	$routes->post('Import_Ligne_Budgetaire/importfile','Import_Ligne_Budgetaire::importfile');
	// ----------------------- Fin Lignes Budgétaires nouveau format----------------------------------------
	
	// ----------------------- Debut Sous Titres nouveau format--------------------------------------
	$routes->get('Import_Ptba_Sous_Titre','Import_Ptba_Sous_Titre::index');
	$routes->post('Import_Ptba_Sous_Titre/importfile','Import_Ptba_Sous_Titre::importfile');
	// ----------------------- Fin  Sous Titres nouveau format----------------------------------------

	// ----------------------- Debut PTBA nouveau format--------------------------------------
	$routes->get('Ptba_Nouveau_Format','Ptba_Nouveau_Format::index');
	$routes->post('Ptba_Nouveau_Format/importfile','Ptba_Nouveau_Format::importfile');
	// ----------------------- Fin PTBA nouveau format----------------------------------------

	// ------------------------------- Debut Traitement du ptba -----------------------------------
	$routes->get('Traitement_ptba/traite_inst_pro_action','Traitement_ptba::traite_inst_pro_action');
	$routes->get('Traitement_ptba/traite_code_nomeclature','Traitement_ptba::traite_code_nomeclature');
	$routes->get('Traitement_ptba/traite_code_nomeclature_execution','Traitement_ptba::traite_code_nomeclature_execution');
	// ------------------------------- Fin Traitement du ptba -------------------------------------
	// -----------------------PEV PAA activite---------------------------------------------------
	$routes->get('Paa_activite','Paa_activite::index');
	$routes->post('Paa_activite/importfile','Paa_activite::importfile');
	// ------------------------Croissement raccrochage BDD13-------------------------------------
	$routes->get('Croissement_Data_PTBA/croissement_data_bdd_raccrochage','Croissement_Data_PTBA::croissement_data_bdd_raccrochage');
	$routes->get('Croissement_Data_PTBA/croissement_data_bdd_raccrochage','Croissement_Data_PTBA::croissement_data_bdd_raccrochage');
	$routes->get('Croissement_Data_PTBA/bdd13_raccrochage_meme_activite/(:any)/(:any)', 'Croissement_Data_PTBA::bdd13_raccrochage_meme_activite/$1/$2');
	$routes->get('Croissement_Data_PTBA/croissement_data_bdd_raccrochage_montant_different','Croissement_Data_PTBA::croissement_data_bdd_raccrochage_montant_different');
	// -----------------------Croissement raccrochage BDD13--------------------------------------
	$routes->get('Donnees_Ptba_bdd13/comparaison_ptba_bdd13_et_ptba','Donnees_Ptba_bdd13::comparaison_ptba_bdd13_et_ptba');
	$routes->get('Donnees_Ptba_bdd13','Donnees_Ptba_bdd13::ptba_bdd_vue');
	$routes->post('Donnees_Ptba_bdd13/liste_ptba_bdd','Donnees_Ptba_bdd13::liste_ptba_bdd');
	$routes->get('Traite_Donnees','Traite_Donnees::liste_vue');
	$routes->post('Traite_Donnees/liste_ptbas','Traite_Donnees::liste_ptbas');
	// ===========================================================
	//Debut importation data execution budgetaire T2
	$routes->get('Executionbudget_tdeux','Executionbudget_tdeux::index');
	$routes->post('Executionbudget_tdeux/importfile','Executionbudget_tdeux::importfile');
	$routes->get('Executionbudget_Trimestre_Deux','Executionbudget_Trimestre_Deux::index');
	$routes->post('Executionbudget_Trimestre_Deux/importfile','Executionbudget_Trimestre_Deux::importfile');
	//Fin importation data execution budgetaire T2

	//Debut importation data ptba
	$routes->get('Data_Ptba_Revise','Data_Ptba_Revise::index');
	$routes->post('Data_Ptba_Revise/importfile','Data_Ptba_Revise::importfile');
	//Fin importation data ptba

	//Debut importation data execution budgetaire
	$routes->get('Data_ptba','Data_ptba::index');
	$routes->post('Data_ptba/importfile','Data_ptba::importfile');
	//Fin importation data execution budgetaire

	//Debut importation data execution budgetaire
	$routes->get('Executionbudget','Executionbudget::index');
	$routes->post('Executionbudget/importfile','Executionbudget::importfile');
	//Fin importation data execution budgetaire

	$routes->get('Gestion_Data_Ptba/index','Gestion_Data_Ptba::index');
	$routes->post('Gestion_Data_Ptba/charge_ptba','Gestion_Data_Ptba::charge_ptba');
	$routes->get('Gestion_Data_Ptba/index_ptba_revise','Gestion_Data_Ptba::index_ptba_revise');
	$routes->post('Gestion_Data_Ptba/charge_ptba_revise','Gestion_Data_Ptba::charge_ptba_revise');
	$routes->get('Gestion_Data_Ptba/index_charge_compare','Gestion_Data_Ptba::index_charge_compare');
	$routes->post('Gestion_Data_Ptba/charge_compare_activite_ptba','Gestion_Data_Ptba::charge_compare_activite_ptba');
	$routes->get('Gestion_Data_Ptba/compare_activite_ptba','Gestion_Data_Ptba::compare_activite_ptba');
	$routes->get('Gestion_Data_Ptba/calculer_montant_utilise','Gestion_Data_Ptba::calculer_montant_utilise');
	$routes->get('Gestion_Data_Ptba/change_activite_ptba','Gestion_Data_Ptba::change_activite_ptba');
	$routes->get('Gestion_Data_Ptba/chargement_user','Gestion_Data_Ptba::chargement_user');
	$routes->get('Gestion_Data_Ptba/save_activite_ptba','Gestion_Data_Ptba::save_activite_ptba');
	$routes->get('Gestion_Data_Ptba/index_data_raccrochage','Gestion_Data_Ptba::index_data_raccrochage');
	$routes->post('Gestion_Data_Ptba/liste__data_raccrochage','Gestion_Data_Ptba::liste__data_raccrochage');
	$routes->get('Gestion_Data_Ptba/get_activite/(:any)','Gestion_Data_Ptba::get_activite/$1');
});
############### Fin module donnees ##################

############### module double commande new ##################
$routes->group('double_commande_new', ['namespace' => 'App\Modules\double_commande_new\Controllers'], function ($routes)
{
	$routes->get('Menu_Engagement_Juridique/exporter_Excel_deja_fait/(:any)','Menu_Engagement_Juridique::exporter_Excel_deja_fait/$1');
	$routes->get('Liquidation_Double_Commande/exporter_Excel_deja_fait/(:any)','Liquidation_Double_Commande::exporter_Excel_deja_fait/$1');

	$routes->post('Phase_comptable/add_cart','Phase_comptable::add_cart');
	$routes->post('Phase_comptable/delete_cart','Phase_comptable::delete_cart');
	$routes->post('Phase_comptable/detruire_cart','Phase_comptable::detruire_cart');
	
	$routes->get('Liste_transmission_bordereau_deja_transmis_brb/exporter_Excel/(:any)','Liste_transmission_bordereau_deja_transmis_brb::exporter_Excel/$1');
	$routes->get('Liste_transmission_bordereau_deja_transmis_brb/get_sous_titre/(:any)','Liste_transmission_bordereau_deja_transmis_brb::get_sous_titre/$1');
	$routes->post('Liste_Paiement/change_count','Liste_Paiement::change_count');
	$routes->get('Script_Faire_Historique_Ptba/traitement', 'Script_Faire_Historique_Ptba::traitement');
    $routes->get('Script_Faire_Historique_Ptba/inserer_historique', 'Script_Faire_Historique_Ptba::inserer_historique');
	$routes->get('Croisement_Tache/get_view/(:any)','Croisement_Tache::index/$1');
	$routes->post('Croisement_Tache/save','Croisement_Tache::save');
	$routes->post('Croisement_Tache/get_info','Croisement_Tache::get_info');
	
	$routes->get('Script_Migration_V3_Vers_Prod/traitement','Script_Migration_V3_Vers_Prod::traitement');
	$routes->get('PTBA_Revise_Upload/nettoyage_ptba','PTBA_Revise_Upload::nettoyage_ptba');
	$routes->get('PTBA_Revise_Upload/nettoyage_activite_pap','PTBA_Revise_Upload::nettoyage_activite_pap');
	$routes->get('PTBA_Revise_Upload/nettoyage_activite_costab','PTBA_Revise_Upload::nettoyage_activite_costab');
	$routes->get('PTBA_Revise_Upload/nettoyage_pnd_indicateur','PTBA_Revise_Upload::nettoyage_pnd_indicateur');

	$routes->get('Suivi_Execution/exporter_Excel_Eng_Budg_Corr/(:any)','Suivi_Execution::exporter_Excel_Eng_Budg_Corr/$1');
	$routes->get('Suivi_Execution/exporter_Excel_Eng_Budg_valider/(:any)','Suivi_Execution::exporter_Excel_Eng_Budg_valider/$1');	
	$routes->get('Suivi_Execution/exporter_Excel_Eng_Jur_Faire/(:any)','Suivi_Execution::exporter_Excel_Eng_Jur_Faire/$1');
	$routes->get('Suivi_Execution/exporter_Excel_Eng_Jur_corriger/(:any)','Suivi_Execution::exporter_Excel_Eng_Jur_corriger/$1');
	$routes->get('Suivi_Execution/exporter_ordo_entente/(:any)','Suivi_Execution::exporter_ordo_entente/$1');
	$routes->get('Suivi_Execution/exporter_prise_attente_reception/(:any)','Suivi_Execution::exporter_prise_attente_reception/$1');
	$routes->get('Suivi_Execution/Excel_titre_attente_recep_dir_compt/(:any)','Suivi_Execution::Excel_titre_attente_recep_dir_compt/$1');
	$routes->get('Suivi_Execution/excel_decais_attente_recep_brb/(:any)','Suivi_Execution::excel_decais_attente_recep_brb/$1');
	$routes->get('Suivi_Execution/excel_decais_attente_traitement/(:any)','Suivi_Execution::excel_decais_attente_traitement/$1');
	$routes->get('Suivi_Execution/excel_Eng_jurd_Valider/(:any)','Suivi_Execution::excel_Eng_jurd_Valider/$1');
	$routes->get('Suivi_Execution/excel_liquidation_faire/(:any)','Suivi_Execution::excel_liquidation_faire/$1');
	$routes->get('Suivi_Execution/excel_liquidation_corriger/(:any)','Suivi_Execution::excel_liquidation_corriger/$1');
	$routes->get('Suivi_Execution/excel_liquidation_valider/(:any)','Suivi_Execution::excel_liquidation_valider/$1');
	$routes->get('Suivi_Execution/Excel_titre_attente_correction/(:any)','Suivi_Execution::Excel_titre_attente_correction/$1');
	$routes->get('Suivi_Execution/exporter_td_attente_etablissemnt/(:any)','Suivi_Execution::exporter_td_attente_etablissemnt/$1');
	$routes->get('Suivi_Execution/Excel_titre_attente_reception_obr/(:any)','Suivi_Execution::Excel_titre_attente_reception_obr/$1');

	$routes->get('Paiement_Salaire_Liste/exporter_Excel_prise_charge/(:any)','Paiement_Salaire_Liste::exporter_Excel_prise_charge/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_td_salaire_net/(:any)','Paiement_Salaire_Liste::exporter_Excel_td_salaire_net/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_td_autre_retenu/(:any)','Paiement_Salaire_Liste::exporter_Excel_td_autre_retenu/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_sign_dir_compt/(:any)','Paiement_Salaire_Liste::exporter_Excel_sign_dir_compt/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_sign_DGFP/(:any)','Paiement_Salaire_Liste::exporter_Excel_sign_DGFP/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_sign_Ministre/(:any)','Paiement_Salaire_Liste::exporter_Excel_sign_Ministre/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_validate_td_salaire_net/(:any)','Paiement_Salaire_Liste::exporter_Excel_validate_td_salaire_net/$1');
	$routes->get('Paiement_Salaire_Liste/exporter_Excel_validate_td_salaire_autre_retenu/(:any)','Paiement_Salaire_Liste::exporter_Excel_validate_td_salaire_autre_retenu/$1');
	$routes->get('Decaissement_Salaire_Liste/exporter_Excel_decaissement_fait/(:any)','Decaissement_Salaire_Liste::exporter_Excel_decaissement_fait/$1');
	$routes->get('Etat_avancement/exporter_Excel/(:any)','Etat_avancement::exporter_Excel/$1');

	$routes->get('Ordonnancement_Salaire_Liste/exporter_Excel_Ordo_a_faire/(:any)','Ordonnancement_Salaire_Liste::exporter_Excel_Ordo_a_faire/$1');
	$routes->get('Ordonnancement_Salaire_Liste/exporter_Excel_Ordo_deja_fait/(:any)','Ordonnancement_Salaire_Liste::exporter_Excel_Ordo_deja_fait/$1');
	$routes->get('Liquidation_Salaire_Liste/exporter_Excel_deja_fait/(:any)','Liquidation_Salaire_Liste::exporter_Excel_deja_fait/$1');
	$routes->get('Liquidation_Salaire_Liste/exporter_Excel_A_corriger/(:any)','Liquidation_Salaire_Liste::exporter_Excel_A_corriger/$1');
	$routes->get('Liquidation_Salaire_Liste/exporter_Excel_A_valider/(:any)','Liquidation_Salaire_Liste::exporter_Excel_A_valider/$1');
	$routes->get('Liquidation_Salaire_Liste/exporter_Excel_deja_valider/(:any)','Liquidation_Salaire_Liste::exporter_Excel_deja_valider/$1');
	$routes->get('Upload_Ptba_Revise/get_view','Upload_Ptba_Revise::get_view');
	$routes->post('Upload_Ptba_Revise/save','Upload_Ptba_Revise::save');

    //debut doublants
	$routes->add('Doublants/doublant_pap_activite','Doublants::doublant_pap_activite');
	$routes->add('Doublants/doublant_costab_activite','Doublants::doublant_costab_activite');
	//fin doublants

	//debut upload ptba
	$routes->add('PTBA_Upload','PTBA_Upload::index');
	$routes->post('PTBA_Upload/save_upload','PTBA_Upload::save_upload');

	$routes->get('PTBA_Upload/get_view','PTBA_Upload::index');
    $routes->post('PTBA_Upload/save_upload','PTBA_Upload::save_upload');

    $routes->add('PTBA_Revise_Upload','PTBA_Revise_Upload::index');
    $routes->post('PTBA_Revise_Upload/save_upload','PTBA_Revise_Upload::save_upload');
    $routes->get('Modifier_Tache/revise','Modifier_Tache::get_view_revise');
    $routes->get('Modifier_Tache/revise/(:any)','Modifier_Tache::get_view_revise/$1');
    $routes->post('Modifier_Tache/update_revise','Modifier_Tache::update_revise');
    $routes->post('Modifier_Tache/update','Modifier_Tache::update');
    $routes->get('Modifier_Tache/get_taches/(:any)','Modifier_Tache::get_taches/$1');
    $routes->get('Modifier_Tache','Modifier_Tache::get_view');
    $routes->get('Modifier_Tache/(:any)','Modifier_Tache::get_view/$1');
    $routes->post('Liste_croisement_ptba_ptba_revise/detail_task','Liste_croisement_ptba_ptba_revise::detail_task');
    $routes->post('Liste_croisement_ptba_ptba_revise/listing','Liste_croisement_ptba_ptba_revise::listing');
    $routes->get('Liste_croisement_ptba_ptba_revise','Liste_croisement_ptba_ptba_revise::index');
    $routes->post('Liste_ptba_orginal/listing','Liste_ptba_orginal::listing');
    $routes->get('Liste_ptba_orginal','Liste_ptba_orginal::index');
    $routes->post('Liste_ptba_revise/listing','Liste_ptba_revise::listing');
    $routes->get('Liste_ptba_revise','Liste_ptba_revise::index');
    $routes->post('Liste_tache_trouve/listing','Liste_tache_trouve::listing');
    $routes->get('Liste_tache_trouve','Liste_tache_trouve::index');
	//fin upload ptba
    $routes->get('Liste_transmission_bordereau_a_transmettre_brb/exporter_Excel/(:any)','Liste_transmission_bordereau_a_transmettre_brb::exporter_Excel/$1');
	$routes->get('Liste_Paiement/exporter_Excel_sign_td_dgfp/(:any)','Liste_Paiement::exporter_Excel_sign_td_dgfp/$1');
	$routes->get('Liste_Paiement/exporter_Excel_sign_td_ministre/(:any)','Liste_Paiement::exporter_Excel_sign_td_ministre/$1');
	$routes->get('Liste_Reception_Prise_Charge/exporter_Excel_deja_recep/(:any)','Liste_Reception_Prise_Charge::exporter_Excel_deja_recep/$1');
	$routes->get('List_Bordereau_Deja_Transmsis/exporter_Excel/(:any)','List_Bordereau_Deja_Transmsis::exporter_Excel/$1');
	$routes->get('Controles_Decaissement/exporter_excel_controle_brb/(:any)','Controles_Decaissement::exporter_excel_controle_brb/$1');
	$routes->get('Controles_Decaissement/exporter_excel_controle_a_transmettre/(:any)','Controles_Decaissement::exporter_excel_controle_a_transmettre/$1');

    $routes->get('Generate_Note/generate_note_plusieur/(:any)','Generate_note::generate_note_plusieur/$1');
	$routes->get('Liquidation_Salaire/add_autre_retenu','Liquidation_Salaire::add_autre_retenu');
	$routes->post('Liquidation_Salaire/save_autre_retenu','Liquidation_Salaire::save_autre_retenu');
	$routes->post('Liquidation_Salaire/save_tempo','Liquidation_Salaire::save_tempo');
	$routes->post('Liquidation_Salaire/delete','Liquidation_Salaire::delete');
	$routes->post('Liquidation_Salaire/save_autre_retenu','Liquidation_Salaire::save_autre_retenu');
	$routes->get('Liquidation_Salaire_Liste/liste_autre_retenu','Liquidation_Salaire_Liste::liste_autre_retenu');
	$routes->post('Liquidation_Salaire_Liste/listing_autre_retenu','Liquidation_Salaire_Liste::listing_autre_retenu');
	
    $routes->post('Signataire_Note/save_newPoste','Signataire_Note::save_newPoste');
    $routes->add('Signataire_Note/get_view','Signataire_Note::get_view');
    $routes->add('Signataire_Note/get_update_view/(:any)','Signataire_Note::get_update_view/$1');
	$routes->post('Signataire_Note/getSousTutel','Signataire_Note::getSousTutel');
	$routes->post('Signataire_Note/update_sign','Signataire_Note::update_sign');
	$routes->post('Signataire_Note/save_tempo','Signataire_Note::save_tempo');
	$routes->post('Signataire_Note/save','Signataire_Note::save');
	$routes->post('Signataire_Note/delete','Signataire_Note::delete');
	$routes->add('Signataire_Note/liste','Signataire_Note::liste');
	$routes->post('Signataire_Note/listing','Signataire_Note::listing');
	
   $routes->add('Montant_Execution_Par_Tache/get_liste','Montant_Execution_Par_Tache::get_liste');
   $routes->post('Montant_Execution_Par_Tache/listing','Montant_Execution_Par_Tache::listing');
   $routes->post('Montant_Execution_Par_Tache/get_prog','Montant_Execution_Par_Tache::get_prog');
   $routes->post('Montant_Execution_Par_Tache/change_count','Montant_Execution_Par_Tache::change_count');
	$routes->post('Liquidation_Salaire/get_salarie','Liquidation_Salaire::get_salarie');
  	$routes->post('Controles_Decaissement/save_newMotif','Controles_Decaissement::save_newMotif');
  	$routes->get('Controles_Decaissement/correction_a_transmettre','Controles_Decaissement::correction_a_transmettre');
  	$routes->post('Controles_Decaissement/correction_a_transmettre_listing','Controles_Decaissement::correction_a_transmettre_listing');
  	$routes->get('Reception_TD_Non_Valide','Reception_TD_Non_Valide::index');
  	$routes->post('Reception_TD_Non_Valide/listing','Reception_TD_Non_Valide::listing');
  	$routes->get('Transmission_Brb_MinFin/transmettre', 'Transmission_Brb_MinFin::transmettre');
  	$routes->post("Transmission_Brb_MinFin/save_transmission", "Transmission_Brb_MinFin::save_transmission");
  	$routes->get('Reception_Brb_MinFin/recevoir', 'Reception_Brb_MinFin::recevoir');
  	$routes->post("Reception_Brb_MinFin/save_rec", "Reception_Brb_MinFin::save_rec");
  	$routes->get('Recherche_Mot_Cle/getInfo_sousTitre/(:any)', 'Recherche_Mot_Cle::getInfo_sousTitre/$1');
  	$routes->get('Recherche_Mot_Cle/getInfo_ligne/(:any)', 'Recherche_Mot_Cle::getInfo_ligne/$1');
  	$routes->get('Recherche_Mot_Cle/getInfo_activite/(:any)', 'Recherche_Mot_Cle::getInfo_activite/$1');
  	$routes->get('Recherche_Mot_Cle/getInfo_tache/(:any)', 'Recherche_Mot_Cle::getInfo_tache/$1');


    $routes->get('Modifier_Objet_Engag/index','Modifier_Objet_Engag::index');
	$routes->post('Modifier_Objet_Engag/save','Modifier_Objet_Engag::save');
	$routes->get('Modifier_Objet_Engag/get_info/(:any)','Modifier_Objet_Engag::get_info/$1');
	$routes->get('Modifier_Prestataire/get_info/(:any)','Modifier_Prestataire::get_info/$1');
	$routes->get('Modifier_Prestataire/get_view','Modifier_Prestataire::get_view');
    $routes->post('Modifier_Prestataire/get_prestataire/(:any)','Modifier_Prestataire::get_prestataire/$1');
    $routes->post('Modifier_Prestataire/update','Modifier_Prestataire::update');
    $routes->post('Modifier_Prestataire/update/(:any)','Modifier_Prestataire::update/$1');
    $routes->post('Liquidation_Salaire/save_newMotif', 'Liquidation_Salaire::save_newMotif');
    $routes->add('Suivi_Execution/prise_charge_attente_reception','Suivi_Execution::prise_charge_attente_reception');
    $routes->post('Suivi_Execution/listing_prise_charge_attente_reception','Suivi_Execution::listing_prise_charge_attente_reception');
    $routes->add('Suivi_Execution/titre_attente_etablissement','Suivi_Execution::titre_attente_etablissement');
    $routes->post('Suivi_Execution/listing_titre_attente_etablissement','Suivi_Execution::listing_titre_attente_etablissement');
    $routes->add('Suivi_Execution/titre_attente_correction','Suivi_Execution::titre_attente_correction');
    $routes->post('Suivi_Execution/listing_titre_attente_correction','Suivi_Execution::listing_titre_attente_correction');
    $routes->add('Suivi_Execution/titre_attente_reception_dir_compt','Suivi_Execution::titre_attente_reception_dir_compt');
    $routes->post('Suivi_Execution/listing_titre_attente_reception_dir_compt','Suivi_Execution::listing_titre_attente_reception_dir_compt');
    $routes->add('Suivi_Execution/titre_attente_reception_obr','Suivi_Execution::titre_attente_reception_obr');
    $routes->post('Suivi_Execution/listing_titre_attente_reception_obr','Suivi_Execution::listing_titre_attente_reception_obr');

    $routes->add('Suivi_Execution/decais_attente_traitement','Suivi_Execution::decais_attente_traitement');
    $routes->post('Suivi_Execution/listing_decais_attente_traitement','Suivi_Execution::listing_decais_attente_traitement');
    $routes->add('Suivi_Execution/decais_attente_recep_brb','Suivi_Execution::decais_attente_recep_brb');
    $routes->post('Suivi_Execution/listing_decais_attente_recep_brb','Suivi_Execution::listing_decais_attente_recep_brb');

    $routes->post('Liquidation_Salaire/save_newBenef','Liquidation_Salaire::save_newBenef');
    $routes->get('Paiement_Salaire_Liste','Paiement_Salaire_Liste::index');
    $routes->post('Etat_avancement/detail_task','Etat_avancement::detail_task');
    $routes->get('Ordonnancement_Double_Commande/exporter_deja_ordonnance/(:any)','Ordonnancement_Double_Commande::exporter_deja_ordonnance/$1');
    $routes->post('Ordonnancement_Double_Commande/change_count','Ordonnancement_Double_Commande::change_count');
    $routes->post('Phase_Administrative_Budget/get_TacheMoneyCorrection/(:any)','Phase_Administrative_Budget::get_TacheMoneyCorrection/$1');
    $routes->get('Ordonna_Dir_Budg_Vers_Ced/liste','Ordonna_Dir_Budg_Vers_Ced::index');
	$routes->post('Ordonna_Dir_Budg_Vers_Ced/listing','Ordonna_Dir_Budg_Vers_Ced::listing');
	$routes->get('Ordonna_Dir_Budg_Vers_Ced/add','Ordonna_Dir_Budg_Vers_Ced::add');
	$routes->post('Ordonna_Dir_Budg_Vers_Ced/save','Ordonna_Dir_Budg_Vers_Ced::save');
	$routes->post('Ordonna_Dir_Budg_Vers_Ced/get_sous_titre','Ordonna_Dir_Budg_Vers_Ced::get_sous_titre');

    $routes->get('Phase_comptable/sign_titre_retour_correction/(:any)','Phase_comptable::sign_titre_retour_correction/$1');
	$routes->post('Phase_comptable/save_sign_titre_retour_correction','Phase_comptable::save_sign_titre_retour_correction');

	$routes->get('Phase_comptable/correction_pc_etablissement/(:any)','Phase_comptable::correction_pc_etablissement/$1');
	
	$routes->post('Phase_comptable/save_correction_pc_etablissement','Phase_comptable::save_correction_pc_etablissement');

	$routes->get('Liste_Paiement/vue_correct_etape','Liste_Paiement::vue_correct_etape');
	$routes->post('Liste_Paiement/listing_correct_etape','Liste_Paiement::listing_correct_etape');

	$routes->get('Liquidation_Salaire_Liste/index_Deja_Fait','Liquidation_Salaire_Liste::index_Deja_Fait');
	$routes->post('Liquidation_Salaire_Liste/listing_Deja_Fait','Liquidation_Salaire_Liste::listing_Deja_Fait');
	$routes->get('Liquidation_Salaire/add_benef','Liquidation_Salaire::add_benef');
	$routes->post('Liquidation_Salaire/save_benef','Liquidation_Salaire::save_benef');
   $routes->get('Menu_Engagement_Budgetaire/annuler_sans_bon/(:any)','Menu_Engagement_Budgetaire::annuler_sans_bon/$1');
   
	$routes->add('Menu_Engagement_Budgetaire/rejete_fin','Menu_Engagement_Budgetaire::rejete_fin');
	$routes->post('Menu_Engagement_Budgetaire/listing_fin_rejette','Menu_Engagement_Budgetaire::listing_fin_rejette');

	$routes->add('Menu_Engagement_Budgetaire/rejete_fin','Menu_Engagement_Budgetaire::rejete_fin');
	$routes->post('Ordonnancement_Double_Commande/detail_task','Ordonnancement_Double_Commande::detail_task');

	$routes->post('Liquidation_Double_Commande/detail_task','Liquidation_Double_Commande::detail_task');

	$routes->post('Menu_Engagement_Juridique/detail_task','Menu_Engagement_Juridique::detail_task');

	$routes->post('Menu_Engagement_Budgetaire/detail_task','Menu_Engagement_Budgetaire::detail_task');
	//------------ engagement des salaires ------------------------------------------------------
	$routes->post('Decaissement_Salaire_Liste/listing_decaissement_deja_fait','Decaissement_Salaire_Liste::listing_decaissement_deja_fait');
	$routes->get('Decaissement_Salaire_Liste/vue_decaiss_faire','Decaissement_Salaire_Liste::vue_decaiss_faire');
	$routes->post('Decaissement_Salaire_Liste/listing_decaissement_faire','Decaissement_Salaire_Liste::listing_decaissement_faire');
	$routes->post('Phase_Comptable_Salaire/save_signature_titre_dgfp', 'Phase_Comptable_Salaire::save_signature_titre_dgfp');

	$routes->get('Decaissement_Salaire_Liste/vue_decaiss_faits','Decaissement_Salaire_Liste::vue_decaiss_faits');
	$routes->get('Decaissement_Salaire/index_dec_salaire/(:any)','Decaissement_Salaire::index_dec_salaire/$1');	
	$routes->post('Paiement_Salaire_Liste/listing_td_Salaire_Net','Paiement_Salaire_Liste::listing_td_Salaire_Net');
	$routes->post('Paiement_Salaire_Liste/listing_td_Autre_Retenu','Paiement_Salaire_Liste::listing_td_Autre_Retenu');
	$routes->get('Decaissement_Salaire_Liste/vue_decaiss_faire','Decaissement_Salaire_Liste::vue_decaiss_faire');
	$routes->post('Decaissement_Salaire_Liste/listing_decaissement_faire','Decaissement_Salaire_Liste::listing_decaissement_faire');
	$routes->get('Paiement_Salaire_Liste/vue_td_Salaire_Net','Paiement_Salaire_Liste::vue_td_Salaire_Net');
	$routes->get('Paiement_Salaire_Liste/vue_td_Autres_Retenus','Paiement_Salaire_Liste::vue_td_Autres_Retenus');

	$routes->get('Decaissement_Salaire_Liste/vue_decaiss_faits','Decaissement_Salaire_Liste::vue_decaiss_faits');
	$routes->post('Decaissement_Salaire_Liste/listing_decaissement_deja_fait','Decaissement_Salaire_Liste::listing_decaissement_deja_fait');

	$routes->get('Decaissement_Salaire/index_dec_salaire/(:any)','Decaissement_Salaire::index_dec_salaire/$1');
	$routes->post('Decaissement_Salaire/save_dec_salaire','Decaissement_Salaire::save_dec_salaire');

	$routes->get('Validation_TD_Salaire/vue_valid_titre_net/(:any)','Validation_TD_Salaire::vue_valid_titre_net/$1');
	$routes->post('Validation_TD_Salaire/save_valid_titre_net','Validation_TD_Salaire::save_valid_titre_net');
	$routes->get('Validation_TD_Salaire/vue_valid_titre_autre/(:any)','Validation_TD_Salaire::vue_valid_titre_autre/$1');
	$routes->post('Validation_TD_Salaire/save_valid_titre_autre','Validation_TD_Salaire::save_valid_titre_autre');
	$routes->add('Phase_Comptable_Salaire/prise_Charge/(:any)','Phase_Comptable_Salaire::prise_Charge/$1');
	$routes->post('Phase_Comptable_Salaire/save_prise_Charge','Phase_Comptable_Salaire::save_prise_Charge');
	$routes->get('Phase_Comptable_Salaire/vue_prise_charge','Phase_Comptable_Salaire::vue_prise_charge');
	$routes->get('Phase_Comptable_Salaire/get_sous_titre/(:any)','Phase_Comptable_Salaire::get_sous_titre/$1');
	$routes->post('Phase_Comptable_Salaire/listing_prise_charge','Phase_Comptable_Salaire::listing_prise_charge');

	$routes->add('Phase_Comptable_Salaire/etablir_titre/(:any)','Phase_Comptable_Salaire::etablir_titre/$1');
	$routes->add('Phase_Comptable_Salaire/etablir_titre_retenu/(:any)','Phase_Comptable_Salaire::etablir_titre_retenu/$1');

	$routes->add('Phase_Comptable_Salaire/save_edition_TD/(:any)','Phase_Comptable_Salaire::save_edition_TD/$1');

	$routes->get('Phase_Comptable_Salaire/vue_EtablissementTD','Phase_Comptable_Salaire::vue_EtablissementTD');
	$routes->post('Phase_Comptable_Salaire/listing_EtablissementTD','Phase_Comptable_Salaire::listing_EtablissementTD');

	$routes->add('Phase_Comptable_Salaire/insert_tab_tempo','Phase_Comptable_Salaire::insert_tab_tempo');
	$routes->add('Phase_Comptable_Salaire/afficher_cart/(:any)','Phase_Comptable_Salaire::afficher_cart/$1');
	$routes->add('Phase_Comptable_Salaire/delete_InCart/(:any)','Phase_Comptable_Salaire::delete_InCart/$1');
      
	$routes->add('Liquidation_Salaire/add','Liquidation_Salaire::add');
	$routes->get('Liquidation_Salaire/get_sousTutel/(:any)','Liquidation_Salaire::get_sousTutel/$1');
	$routes->post('Liquidation_Salaire/get_data', 'Liquidation_Salaire::get_data');
	$routes->post('Liquidation_Salaire/savesalaire','Liquidation_Salaire::savesalaire');
	$routes->get('Liquidation_Salaire/add_confirm/(:any)','Liquidation_Salaire::add_confirm/$1');
	$routes->post('Liquidation_Salaire/save_confirm','Liquidation_Salaire::save_confirm');
	$routes->get('Liquidation_Salaire/add_correction_view/(:any)','Liquidation_Salaire::add_correction_view/$1');
	$routes->post('Liquidation_Salaire/save_correction_salaire','Liquidation_Salaire::save_correction_salaire');
	$routes->get('Liquidation_Salaire_Liste/index_A_Corr','Liquidation_Salaire_Liste::index_A_Corr');
	$routes->post('Liquidation_Salaire_Liste/listing','Liquidation_Salaire_Liste::listing');



	$routes->get('Liquidation_Salaire_Liste/index_A_valider','Liquidation_Salaire_Liste::index_A_valider');
	$routes->post('Liquidation_Salaire_Liste/listing_A_Valide','Liquidation_Salaire_Liste::listing_A_Valide');
	
	$routes->get('Liquidation_Salaire_Liste/listing_inst/(:any)','Liquidation_Salaire_Liste::listing_inst/$1');
	$routes->get('Liquidation_Salaire_Liste/index_Deja_valider','Liquidation_Salaire_Liste::index_Deja_valider');
	$routes->post('Liquidation_Salaire_Liste/listing_Deja_Valide','Liquidation_Salaire_Liste::listing_Deja_Valide');

	$routes->get('Ordonnancement_Salaire_Liste/index_Deja_Fait','Ordonnancement_Salaire_Liste::index_Deja_Fait');
	$routes->post('Ordonnancement_Salaire_Liste/listing_Deja_Fait','Ordonnancement_Salaire_Liste::listing_Deja_Fait');

	$routes->post('Liquidation_Salaire_Liste/listing_tache','Liquidation_Salaire_Liste::listing_tache');
	$routes->post('Liquidation_Salaire/listing_st','Liquidation_Salaire::listing_st');
	$routes->post('Liquidation_Salaire/listing_par_sous_titre','Liquidation_Salaire::listing_par_sous_titre');
	$routes->get('Ordonnancement_Salaire_Liste/index_A_Faire','Ordonnancement_Salaire_Liste::index_A_Faire');
	$routes->post('Ordonnancement_Salaire_Liste/listing','Ordonnancement_Salaire_Liste::listing');
	$routes->get('Ordonnancement_Salaire/add/(:any)','Ordonnancement_Salaire::add/$1');
	$routes->post('Ordonnancement_Salaire/save','Ordonnancement_Salaire::save');

	$routes->post('Paiement_Salaire_Liste/listing_prise_charge','Paiement_Salaire_Liste::listing_prise_charge');
	$routes->get('Paiement_Salaire_Liste/vue_prise_charge','Paiement_Salaire_Liste::vue_prise_charge');
	$routes->get('Paiement_Salaire_Liste/vue_sign_dir_compt','Paiement_Salaire_Liste::vue_sign_dir_compt');
	$routes->post('Paiement_Salaire_Liste/listing_sign_dir_compt','Paiement_Salaire_Liste::listing_sign_dir_compt');
	$routes->get('Paiement_Salaire_Liste/vue_sign_dgfp','Paiement_Salaire_Liste::vue_sign_dgfp');
	$routes->post('Paiement_Salaire_Liste/listing_sign_dgfp','Paiement_Salaire_Liste::listing_sign_dgfp');
	$routes->get('Paiement_Salaire_Liste/vue_sign_ministre','Paiement_Salaire_Liste::vue_sign_ministre');
	$routes->post('Paiement_Salaire_Liste/listing_sign_ministre','Paiement_Salaire_Liste::listing_sign_ministre');
	$routes->get('Paiement_Salaire_Liste/vue_valide_td_net','Paiement_Salaire_Liste::vue_valide_td_net');
	$routes->post('Paiement_Salaire_Liste/listing_valide_td_net','Paiement_Salaire_Liste::listing_valide_td_net');
	$routes->get('Paiement_Salaire_Liste/vue_valide_td_autre_retenu','Paiement_Salaire_Liste::vue_valide_td_autre_retenu');
	$routes->post('Paiement_Salaire_Liste/listing_valide_td_autre_retenu','Paiement_Salaire_Liste::listing_valide_td_autre_retenu');

	$routes->get('Phase_comptable_Salaire/signature_titre_min/(:any)','Phase_comptable_Salaire::signature_titre_min/$1');
	$routes->post('Phase_Comptable_Salaire/save_signature_titre_min','Phase_comptable_Salaire::save_signature_titre_min');

	$routes->get('Phase_comptable_Salaire/signature_titre_dir_compt/(:any)', 'Phase_comptable_Salaire::signature_titre_dir_compt/$1');
	$routes->post('Phase_comptable_Salaire/save_signature_titre_dir_compt', 'Phase_comptable_Salaire::save_signature_titre_dir_compt');

	$routes->get('Phase_comptable_Salaire/signature_titre_dgfp/(:any)', 'Phase_comptable_Salaire::signature_titre_dgfp/$1');
	$routes->post('Phase_comptable_Salaire/save_signature_titre_dgfp', 'Phase_comptable_Salaire::save_save_signature_titre_dgfp');
	//------------ engagement des salaires ------------------------------------------------------
	
	//----------- Introduction d'engagement budgétaire avec plusieurs tâches---------------------------
	$routes->post('Liste_Decaissement/detail_task_dec','Liste_Decaissement::detail_task_dec');
	$routes->post('Menu_Engagement_Budgetaire/detail_task','Menu_Engagement_Budgetaire::detail_task');
	$routes->post('Introduction_Budget_Multi_Taches/get_docs/(:any)', 'Introduction_Budget_Multi_Taches::get_docs/$1');
	$routes->post('Introduction_Budget_Multi_Taches/get_TacheMoney/(:any)','Introduction_Budget_Multi_Taches::get_TacheMoney/$1');
	$routes->post('Introduction_Budget_Multi_Taches/get_inst/(:any)','Introduction_Budget_Multi_Taches::get_inst/$1');
	$routes->get('Introduction_Budget_Multi_Taches/get_activite/(:any)','Introduction_Budget_Multi_Taches::get_activite/$1');
	$routes->get('Introduction_Budget_Multi_Taches/get_taches/(:any)','Introduction_Budget_Multi_Taches::get_taches/$1');

	$routes->get('Introduction_Budget_Multi_Taches/etape1_prime/(:any)','Introduction_Budget_Multi_Taches::etape1_prime/$1');
	$routes->post('Introduction_Budget_Multi_Taches/save_etape1_prime','Introduction_Budget_Multi_Taches::save_etape1_prime');
	$routes->get('Introduction_Budget_Multi_Taches/etape1','Introduction_Budget_Multi_Taches::etape1');
	$routes->post('Introduction_Budget_Multi_Taches/save_etape1','Introduction_Budget_Multi_Taches::save_etape1');
	$routes->get('Introduction_Budget_Multi_Taches/get_sousTutel/(:any)','Introduction_Budget_Multi_Taches::get_sousTutel/$1');
	$routes->post('Introduction_Budget_Multi_Taches/get_code/(:any)', 'Introduction_Budget_Multi_Taches::get_code/$1');
	$routes->get('Introduction_Budget_Multi_Taches/get_activitesByCode/(:any)','Introduction_Budget_Multi_Taches::get_activitesByCode/$1');
	$routes->post('Introduction_Budget_Multi_Taches/save_etape1', 'Introduction_Budget_Multi_Taches::save_etape1');
	$routes->get('Introduction_Budget_Multi_Taches/corrige_etape1/(:any)', 'Introduction_Budget_Multi_Taches::corrige_etape1/$1');
	$routes->get('Introduction_Budget_Multi_Taches/etape2/(:any)', 'Introduction_Budget_Multi_Taches::etape2/$1');
	$routes->post('Introduction_Budget_Multi_Taches/activiteGet/(:any)','Introduction_Budget_Multi_Taches::activiteGet/$1');
	$routes->post('Introduction_Budget_Multi_Taches/liste_bon_budgetaire','Introduction_Budget_Multi_Taches::liste_bon_budgetaire');
	$routes->get('Introduction_Budget_Multi_Taches/LISTE1','Introduction_Budget_Multi_Taches::LISTE1');
	$routes->post('Introduction_Budget_Multi_Taches/etape1_correction','Introduction_Budget_Multi_Taches::etape1_correction');
	$routes->post('Introduction_Budget_Multi_Taches/save_etape2','Introduction_Budget_Multi_Taches::save_etape2');
	$routes->get('Introduction_Budget_Multi_Taches/etape3/(:any)', 'Introduction_Budget_Multi_Taches::etape3/$1');
	$routes->post('Introduction_Budget_Multi_Taches/get_activitesMoney/(:any)', 'Introduction_Budget_Multi_Taches::get_activitesMoney/$1');

	$routes->post('Introduction_Budget_Multi_Taches/get_taux/(:any)', 'Introduction_Budget_Multi_Taches::get_taux/$1');
	$routes->get('Introduction_Budget_Multi_Taches/is_delete/(:any)', 'Introduction_Budget_Multi_Taches::is_delete/$1');
	$routes->get('Introduction_Budget_Multi_Taches/is_delete_task/(:any)', 'Introduction_Budget_Multi_Taches::is_delete_task/$1');

	$routes->get('Introduction_Budget_Multi_Taches/is_correct_task/(:any)', 'Introduction_Budget_Multi_Taches::is_correct_task/$1');
	$routes->post('Introduction_Budget_Multi_Taches/save_correct_task', 'Introduction_Budget_Multi_Taches::save_correct_task');

	$routes->get('Introduction_Budget_Multi_Taches/is_correct_tempo/(:any)', 'Introduction_Budget_Multi_Taches::is_correct_tempo/$1');
	$routes->post('Introduction_Budget_Multi_Taches/save_correct_tempo', 'Introduction_Budget_Multi_Taches::save_correct_tempo');


	$routes->post('Introduction_Budget_Multi_Taches/valider_liste', 'Introduction_Budget_Multi_Taches::valider_liste');
	$routes->post('Introduction_Budget_Multi_Taches/save_tempo', 'Introduction_Budget_Multi_Taches::save_tempo');
	$routes->post('Introduction_Budget_Multi_Taches/listing_tempo', 'Introduction_Budget_Multi_Taches::listing_tempo');

	$routes->post('Introduction_Budget_Multi_Taches/listing_task', 'Introduction_Budget_Multi_Taches::listing_task');
	//----------- FIN Introduction d'engagement budgétaire avec plusieurs tâches---------------------------

	//----------- Recherche par Mot Cle ----------------------------------------------
	$routes->get('Recherche_Mot_Cle/index', 'Recherche_Mot_Cle::index');
	$routes->post('Recherche_Mot_Cle/getInfo', 'Recherche_Mot_Cle::getInfo');

	//-------------- LISTE DES ANNULATIONS ----------------------------------------------
	$routes->get('Liste_Annulation/annulation_pc','Liste_Annulation::annulation_pc');
	$routes->post('Liste_Annulation/listing_annulation_pc','Liste_Annulation::listing_annulation_pc');

	$routes->get('Liste_Annulation/annulation_ordo','Liste_Annulation::annulation_ordo');
	$routes->post('Liste_Annulation/listing_annulation_ordo','Liste_Annulation::listing_annulation_ordo');

	//-------------- ANNULATION DES ETAPES PRISE EN CHARGE -------------------------------
	$routes->get('Etapes_Annulations/rejeter_prise_en_charge/(:any)','Etapes_Annulations::rejeter_prise_en_charge/$1');
	$routes->post('Etapes_Annulations/save_rejeter','Etapes_Annulations::save_rejeter');
	$routes->post('Etapes_Annulations/save_newMotif','Etapes_Annulations::save_newMotif');

	//-------------- ANNULATION DES ETAPES ORDONNANCEMENT -------------------------------
	$routes->get('Etapes_Annulations/rejeter_ordo/(:any)','Etapes_Annulations::rejeter_ordo/$1');
	$routes->post('Etapes_Annulations/save_rejeter_ordo','Etapes_Annulations::save_rejeter_ordo');

	//-------------- ANNULATION DES ETAPES LIQUIDATION -------------------------------
	$routes->get('Etapes_Annulations/rejeter_liquid/(:any)','Etapes_Annulations::rejeter_liquid/$1');
	$routes->post('Etapes_Annulations/save_rejeter_liquid','Etapes_Annulations::save_rejeter_liquid');

	//-------------- ANNULATION DES ETAPES JURIDIQUE -------------------------------
	$routes->get('Etapes_Annulations/rejeter_jurid/(:any)','Etapes_Annulations::rejeter_jurid/$1');
	$routes->post('Etapes_Annulations/save_rejeter_jurid','Etapes_Annulations::save_rejeter_jurid');

	//-------------- ANNULATION DES ETAPES BUDGETAIRE -------------------------------
	$routes->get('Etapes_Annulations/rejeter_budget/(:any)','Etapes_Annulations::rejeter_budget/$1');
	$routes->post('Etapes_Annulations/save_rejeter_budget','Etapes_Annulations::save_rejeter_budget');

 	$routes->post('Liste_Paiement/detail_task_ordo','Liste_Paiement::detail_task_ordo');
	$routes->post('Liste_Paiement/detail_task_pay','Liste_Paiement::detail_task_pay');

 	$routes->post('Ordonnancement_Double_Commande/detail_task','Ordonnancement_Double_Commande::detail_task');

	$routes->post('Liquidation_Double_Commande/detail_task','Liquidation_Double_Commande::detail_task');

	$routes->post('Menu_Engagement_Juridique/detail_task','Menu_Engagement_Juridique::detail_task');

	$routes->get('Generate_Note/generate_note/(:any)','Generate_note::generate_note/$1');




 	$routes->get('Liste_Decaissement_Deja_Fait/exporter_Excel/(:any)','Liste_Decaissement_Deja_Fait::exporter_Excel/$1');

	$routes->get('Liste_Decaissement_Deja_Fait/generatePdf/(:any)', 'Liste_Decaissement_Deja_Fait::generatePdf/$1');

	$routes->get('Validation_Titre/exporter_Excel/(:any)','Validation_Titre::exporter_Excel/$1');

	$routes->get('Validation_Titre/generatePdf/(:any)', 'Validation_Titre::generatePdf/$1');
  $routes->get('Menu_Engagement_Juridique/exporter_excel_deja_valide/(:any)', 'Menu_Engagement_Juridique::exporter_excel_deja_valide/$1');
  $routes->get('Menu_Engagement_Juridique/exporter_pdf_deja_valide/(:any)', 'Menu_Engagement_Juridique::exporter_pdf_deja_valide/$1');
  $routes->get('Ordonnancement_Double_Commande/exporter_Excel/(:any)','Ordonnancement_Double_Commande::exporter_Excel/$1');

   $routes->get('Ordonnancement_Double_Commande/generatePdf/(:any)', 'Ordonnancement_Double_Commande::generatePdf/$1');

   $routes->get('Liquidation_Double_Commande/exporter_deja_valider/(:any)','Liquidation_Double_Commande::exporter_deja_valider/$1');
	$routes->get('Liquidation_Double_Commande/exporter_deja_valider_excel/(:any)','Liquidation_Double_Commande::exporter_deja_valider_excel/$1');

	$routes->get('Menu_Engagement_Budgetaire/exporter_Excel/(:any)','Menu_Engagement_Budgetaire::exporter_Excel/$1');
	$routes->get('Menu_Engagement_Budgetaire/exporter_Excel_deja_fait/(:any)','Menu_Engagement_Budgetaire::exporter_Excel_deja_fait/$1');
	$routes->get('Menu_Engagement_Budgetaire/generatePdf/(:any)', 'Menu_Engagement_Budgetaire::generatePdf/$1');

$routes->get('Ordonnancement_Double_Commande/is_correction/(:any)', 'Ordonnancement_Double_Commande::is_correction/$1');
$routes->get('Liquidation_Double_Commande/exporter_deja_valider/(:any)','Liquidation_Double_Commande::exporter_deja_valider/$1');
$routes->get('Menu_Engagement_Budgetaire/is_correction/(:any)', 'Menu_Engagement_Budgetaire::is_correction/$1');
 $routes->get('Menu_Engagement_Juridique/is_correction/(:any)', 'Menu_Engagement_Juridique::is_correction/$1');
$routes->get('Liquidation_Double_Commande/is_correction/(:any)', 'Liquidation_Double_Commande::is_correction/$1');
$routes->get('Liste_Paiement/vue_obr','Liste_Paiement::vue_obr');
$routes->post('Liste_Paiement/listing_obr','Liste_Paiement::listing_obr');
$routes->get('Liste_Paiement/vue_prise_charge','Liste_Paiement::vue_prise_charge');
$routes->post('Liste_Paiement/listing_prise_charge','Liste_Paiement::listing_prise_charge');
$routes->get('Liste_Paiement/vue_etab_titre','Liste_Paiement::vue_etab_titre');
$routes->post('Liste_Paiement/listing_etab_titre','Liste_Paiement::listing_etab_titre');
$routes->get('Liste_Paiement/vue_sign_dir_compt','Liste_Paiement::vue_sign_dir_compt');
$routes->post('Liste_Paiement/listing_sign_dir_compt','Liste_Paiement::listing_sign_dir_compt');
$routes->get('Liste_Paiement/vue_sign_dgfp','Liste_Paiement::vue_sign_dgfp');
$routes->post('Liste_Paiement/listing_sign_dgfp','Liste_Paiement::listing_sign_dgfp');
$routes->get('Liste_Paiement/vue_sign_ministre','Liste_Paiement::vue_sign_ministre');
$routes->post('Liste_Paiement/listing_sign_ministre','Liste_Paiement::listing_sign_ministre');

$routes->get('Liquidation_Double_Commande/exporter_excel/(:any)', 'Liquidation_Double_Commande::exporter_excel/$1');
$routes->get('Liquidation_Double_Commande/exporter_pdf/(:any)', 'Liquidation_Double_Commande::exporter_pdf/$1');
    
    $routes->post('Ordonnancement_Double_Commande/getSousTutel', 'Ordonnancement_Double_Commande::getSousTutel');
	$routes->get('Ordonnancement_Double_Commande/exporter_Excel/(:any)','Ordonnancement_Double_Commande::exporter_Excel/$1');
	$routes->get('Ordonnancement_Double_Commande/generatePdf/(:any)', 'Ordonnancement_Double_Commande::generatePdf/$1');
	$routes->post('Transmission_borderau_brb/checkbord','Transmission_borderau_brb::checkbord');
	$routes->post('Phase_Comptable_Directeur_Comptable/checkbord','Phase_Comptable_Directeur_Comptable::checkbord');
	$routes->post('Reception_First_Bord_Transmission/checkbord','Reception_First_Bord_Transmission::checkbord');
	$routes->post('Phase_comptable/save_newBanque', 'Phase_comptable::save_newBanque');
	$routes->get('Liste_Paiement/vue_correct_pc','Liste_Paiement::vue_correct_pc');
	$routes->post('Liste_Paiement/listing_correct_pc','Liste_Paiement::listing_correct_pc');
	$routes->get('Liste_Paiement/vue_correct_etab_titre','Liste_Paiement::vue_correct_etab_titre');
	$routes->post('Liste_Paiement/listing_correct_etab_titre','Liste_Paiement::listing_correct_etab_titre');
	$routes->post('Phase_comptable/save_newMotif', 'Phase_comptable::save_newMotif');
	$routes->post('Phase_Administrative_Budget/save_newMotif','Phase_Administrative_Budget::save_newMotif');
	$routes->post('Phase_Administrative/save_newMotif','Phase_Administrative::save_newMotif');
	$routes->post('Liquidation/save_newMotif', 'Liquidation::save_newMotif');
	$routes->post('Ordonnancement/save_newMotif','Ordonnancement::save_newMotif');
	$routes->post('Phase_Administrative_Budget/get_docs/(:any)', 'Phase_Administrative_Budget::get_docs/$1');
	//ordonnancement ministre
	$routes->get('Transmission_Bon_Cabinet_prise_charge', 'Transmission_Bon_Cabinet_prise_charge::index');
	$routes->post('Transmission_Bon_Cabinet_prise_charge/listing', 'Transmission_Bon_Cabinet_prise_charge::listing');
	$routes->get('Transmission_Bon_Cabinet_prise_charge/transmission','Transmission_Bon_Cabinet_prise_charge::transmission');
	$routes->post('Transmission_Bon_Cabinet_prise_charge/save', 'Transmission_Bon_Cabinet_prise_charge::save');
	$routes->get('Ordonnancement_Ministre', 'Ordonnancement_Ministre::index');
	$routes->get('Ordonnancement_Ministre/liste','Ordonnancement_Ministre::index');
	$routes->post('Ordonnancement_Ministre/listing','Ordonnancement_Ministre::listing');
	$routes->get('Ordonnancement_Ministre/add','Ordonnancement_Ministre::add');
	$routes->post('Ordonnancement_Ministre/save','Ordonnancement_Ministre::save');
	 $routes->get('Liste_Trans_PC_Ministre', 'Liste_Trans_PC_Ministre::index');
	$routes->post('Liste_Trans_PC_Ministre/listing', 'Liste_Trans_PC_Ministre::listing');
	$routes->get('Ordonnancement_Double_Commande/get_ordon_Afaire_sup','Ordonnancement_Double_Commande::get_ordon_Afaire_sup');

	$routes->post('Ordonnancement_Double_Commande/listing_ordon_Afaire_sup', 'Ordonnancement_Double_Commande::listing_ordon_Afaire_sup');
	
 $routes->get('Reception_First_Bord_Transmission/reception_ministre','Reception_First_Bord_Transmission::reception_ministre');
	$routes->post('Reception_First_Bord_Transmission/save_ministre','Reception_First_Bord_Transmission::save_ministre');
	
	$routes->add('Suivi_Execution/engag_budj_corriger','Suivi_Execution::engag_budj_corriger');
	$routes->post('Suivi_Execution/listing_engag_budj_corriger','Suivi_Execution::listing_engag_budj_corriger');
	$routes->post('Suivi_Execution/get_soutut','Suivi_Execution::get_soutut');
	$routes->post('Suivi_Execution/change_count','Suivi_Execution::change_count');
	$routes->add('Suivi_Execution/engag_budj_valide','Suivi_Execution::engag_budj_valide');
	$routes->post('Suivi_Execution/listing_engag_budj_valider','Suivi_Execution::listing_engag_budj_valider');
	$routes->add('Suivi_Execution/engag_jurd_faire','Suivi_Execution::engag_jurd_faire');
	$routes->post('Suivi_Execution/listing_engag_jurd_a_faire','Suivi_Execution::listing_engag_jurd_a_faire');
	$routes->add('Suivi_Execution/engag_jurd_corriger','Suivi_Execution::engag_jurd_corriger');
	$routes->post('Suivi_Execution/listing_engag_jurd_a_corriger','Suivi_Execution::listing_engag_jurd_a_corriger');
	$routes->add('Suivi_Execution/engag_jurd_valide','Suivi_Execution::engag_jurd_valide');
	$routes->post('Suivi_Execution/listing_engag_jurd_a_valider','Suivi_Execution::listing_engag_jurd_a_valider');
	$routes->add('Suivi_Execution/liquidation_faire','Suivi_Execution::liquidation_faire');
	$routes->post('Suivi_Execution/listing_liquidation_faire','Suivi_Execution::listing_liquidation_faire');
	$routes->add('Suivi_Execution/liquidation_corrige','Suivi_Execution::liquidation_corrige');
	$routes->post('Suivi_Execution/listing_liquidation_corrige','Suivi_Execution::listing_liquidation_corrige');
	$routes->add('Suivi_Execution/liquidation_valide','Suivi_Execution::liquidation_valide');
	$routes->post('Suivi_Execution/listing_liquidation_valide','Suivi_Execution::listing_liquidation_valide');
	$routes->add('Suivi_Execution/ordonnance_valide','Suivi_Execution::ordonnance_valide');
	$routes->post('Suivi_Execution/listing_ordonnance_valide','Suivi_Execution::listing_ordonnance_valide');
	// FIN SUIVI EXECUTION

	// Debut generer piece justificatif
	$routes->get('Liquidation/generer_doc_liquidation/(:any)','Liquidation::generer_doc_liquidation/$1');
	// Fin generer piece justificatif
	$routes->get('Ordonnancement_Vers_Ced/liste','Ordonnancement_Vers_Ced::index');
	$routes->get('Ordonnancement_Vers_Ced/add/(:any)','Ordonnancement_Vers_Ced::add/$1');
	$routes->post('Ordonnancement_Vers_Ced/listing','Ordonnancement_Vers_Ced::listing');
	$routes->post('Ordonnancement_Vers_Ced/save','Ordonnancement_Vers_Ced::save');
	$routes->get('Ordonnancement_Vers_Ced/Corrige_From_Ordo/(:any)','Ordonnancement_Vers_Ced::corrige_ced/$1');	
	$routes->post('Ordonnancement_Vers_Ced/save_corrige_ced','Ordonnancement_Vers_Ced::save_corrige_ced');
	//------------- liste Avant PC----------------------
	$routes->get('Liste_Avant_PC','Liste_Avant_PC::index');
	$routes->post('Liste_Avant_PC/listing','Liste_Avant_PC::listing');
  	//------------- liste Avant PC----------------------

	//------------- liste Avant OBR----------------------
	$routes->get('Liste_Avant_OBR','Liste_Avant_OBR::index');
	$routes->post('Liste_Avant_OBR/listing','Liste_Avant_OBR::listing');
  	//------------- liste Avant OBR----------------------

	//------------- Avant OBR----------------------
	$routes->get('Avant_OBR/add','Avant_OBR::index');
	$routes->post('Avant_OBR/save','Avant_OBR::save');
  	//------------- Avant OBR----------------------

  	//------------- Avant PC----------------------
	$routes->get('Avant_PC/add','Avant_PC::index');
	$routes->post('Avant_PC/save','Avant_PC::save');
  	//------------- Avant PC----------------------
	$routes->get('Transfert_Meme_Activite/get_sousTutel/(:any)','Transfert_Meme_Activite::get_sousTutel/$1');
	$routes->post('Transfert_Meme_Activite/get_inst/(:any)','Transfert_Meme_Activite::get_inst/$1');
	$routes->post('Transfert_Meme_Activite/get_code/(:any)', 'Transfert_Meme_Activite::get_code/$1');
	$routes->get('Transfert_Meme_Activite/get_activite1/(:any)','Transfert_Meme_Activite::get_activite1/$1');
	$routes->get('Transfert_Meme_Activite/get_taches/(:any)', 'Transfert_Meme_Activite::get_taches/$1');
	$routes->get('Transfert_Double_Commande/get_sousTutel/(:any)','Transfert_Double_Commande::get_sousTutel/$1');
	$routes->post('Transfert_Double_Commande/get_inst/(:any)','Transfert_Double_Commande::get_inst/$1');
	$routes->post('Transfert_Double_Commande/get_code/(:any)', 'Transfert_Double_Commande::get_code/$1');
	$routes->get('Transfert_Double_Commande/get_activite1/(:any)','Transfert_Double_Commande::get_activite1/$1');
	$routes->get('Transfert_Double_Commande/get_taches/(:any)', 'Transfert_Double_Commande::get_taches/$1');
	$routes->get('Transfert_Double_Commande/get_sousTutel2/(:any)','Transfert_Double_Commande::get_sousTutel2/$1');
	$routes->post('Transfert_Double_Commande/get_inst2/(:any)','Transfert_Double_Commande::get_inst2/$1');
	$routes->post('Transfert_Double_Commande/get_code2/(:any)', 'Transfert_Double_Commande::get_code2/$1');
	$routes->get('Transfert_Double_Commande/get_activite2/(:any)','Transfert_Double_Commande::get_activite2/$1');
	$routes->get('Transfert_Double_Commande/get_taches2/(:any)', 'Transfert_Double_Commande::get_taches2/$1');
	$routes->get('Taux_De_Change','Taux_De_Change::index');
	$routes->get('Taux_De_Change/add','Taux_De_Change::add');
	$routes->get('Taux_De_Change/getOne/(:any)','Taux_De_Change::getOne/$1');
	$routes->post('Taux_De_Change/listing','Taux_De_Change::listing');
	$routes->post('Taux_De_Change/save','Taux_De_Change::save');
	$routes->post('Taux_De_Change/update','Taux_De_Change::update');
	$routes->post('Phase_Administrative_Budget/get_TacheMoney/(:any)','Phase_Administrative_Budget::get_TacheMoney/$1');
	$routes->post('Phase_Administrative_Budget/get_inst/(:any)','Phase_Administrative_Budget::get_inst/$1');
	$routes->get('Phase_Administrative_Budget/get_activite/(:any)','Phase_Administrative_Budget::get_activite/$1');
	$routes->get('Phase_Administrative_Budget/get_taches/(:any)','Phase_Administrative_Budget::get_taches/$1');
	$routes->get('Ordonnancement/vue_ordonnance_ministre/(:any)','Ordonnancement::vue_ordonnance_ministre/$1');
	$routes->post('Ordonnancement/update_etape9','Ordonnancement::update_etape9');
	$routes->get('Ordonnancement/vue_ordonnance_dgbudget/(:any)','Ordonnancement::vue_ordonnance_dgbudget/$1');
	$routes->post('Ordonnancement/update_etapeDG','Ordonnancement::update_etapeDG');

	$routes->get('Transfert_Double_Commande/liste_transfert', 'Transfert_Double_Commande::liste_transfert');
	$routes->post('Transfert_Double_Commande/listing_Transfert', 'Transfert_Double_Commande::listing_Transfert');
	//validation du TD
	$routes->get('Validation_Titre/confirmer/(:any)','Validation_Titre::confirmer/$1');
	$routes->post('Validation_Titre/save_titre_valider','Validation_Titre::save_titre_valider');

	$routes->get('Validation_Titre/liste_valide_faire','Validation_Titre::liste_valide_faire');
	$routes->post('Validation_Titre/liste_validation','Validation_Titre::liste_validation');

	$routes->get('Validation_Titre/liste_valide_termine','Validation_Titre::liste_valide_termine');
	$routes->post('Validation_Titre/liste_validation_termine','Validation_Titre::liste_validation_termine');

	$routes->get('Validation_Titre/get_sous_titre/(:any)','Validation_Titre::get_sous_titre/$1');

	//------------- liste reception dir compt----------------------
	$routes->get('Receptio_Border_Dir_compt','Receptio_Border_Dir_compt::index');
	$routes->post('Receptio_Border_Dir_compt/listing','Receptio_Border_Dir_compt::listing');
	$routes->post('Receptio_Border_Dir_compt/liste_titre_decaissement','Receptio_Border_Dir_compt::liste_titre_decaissement');
	$routes->post('Receptio_Border_Dir_compt/get_path_bord/(:any)','Receptio_Border_Dir_compt::get_path_bord/$1');
  	//------------- liste reception dir compt----------------------

	$routes->get('Liste_Trans_PC', 'Liste_Trans_PC::index');
	$routes->post('Liste_Trans_PC/listing', 'Liste_Trans_PC::listing');
	$routes->get('Liste_Trans_Deja_Fait_PC', 'Liste_Trans_Deja_Fait_PC::index');
	$routes->post('Liste_Trans_Deja_Fait_PC/listing', 'Liste_Trans_Deja_Fait_PC::listing');

	// DEBUT TRANSMISSION ET RECEPTION
	$routes->post('Reception_First_Bord_Transmission/save','Reception_First_Bord_Transmission::save');
	$routes->get('Transmission_borderau_brb','Transmission_borderau_brb::index');
	$routes->get('List_Bordereau_Deja_Transmsis','List_Bordereau_Deja_Transmsis::index');
	$routes->post('List_Bordereau_Deja_Transmsis/listing','List_Bordereau_Deja_Transmsis::listing');

	// liste deja transmis
	$routes->get('Liste_transmission_bordereau_deja_transmis_brb','Liste_transmission_bordereau_deja_transmis_brb::index');
	$routes->post('Liste_transmission_bordereau_deja_transmis_brb/listing','Liste_transmission_bordereau_deja_transmis_brb::listing');
	
	// liste a transmimettre
	$routes->get('Liste_transmission_bordereau_a_transmettre_brb','Liste_transmission_bordereau_a_transmettre_brb::index');
	$routes->post('Liste_transmission_bordereau_a_transmettre_brb/listing','Liste_transmission_bordereau_a_transmettre_brb::listing');
	//---------------debut Liste réceptions - prise en charge ---------------------
	$routes->add('Liste_Reception_Prise_Charge','Liste_Reception_Prise_Charge::index');
	$routes->post('Liste_Reception_Prise_Charge/listing','Liste_Reception_Prise_Charge::listing');
	$routes->post('Liste_Reception_Prise_Charge/detail_bons','Liste_Reception_Prise_Charge::detail_bons');
  	//---------------Fin Liste réceptions - prise en charge --------------

	//---------------debut Liste déjà réceptions - prise en charge ---------------------
	$routes->add('Liste_Reception_Prise_Charge/deja_recep','Liste_Reception_Prise_Charge::deja_recep');
	$routes->post('Liste_Reception_Prise_Charge/listing_deja','Liste_Reception_Prise_Charge::listing_deja');
	$routes->post('Liste_Reception_Prise_Charge/deja_detail_bons','Liste_Reception_Prise_Charge::deja_detail_bons');
  	//---------------Fin Liste déjà réceptions - prise en charge --------------

	//------------- bordereau recu dir comptable----------------------
	$routes->get('Bordereau_Recu_Dir_Comptabilite','Bordereau_Recu_Dir_Comptabilite::index');
	$routes->post('Bordereau_Recu_Dir_Comptabilite/listing','Bordereau_Recu_Dir_Comptabilite::listing');
	$routes->post('Bordereau_Recu_Dir_Comptabilite/liste_titre_decaissement','Bordereau_Recu_Dir_Comptabilite::liste_titre_decaissement');
	$routes->post('Bordereau_Recu_Dir_Comptabilite/get_path_bord/(:any)','Bordereau_Recu_Dir_Comptabilite::get_path_bord/$1');
  	//-------------Fin bordereau recu dir comptable ----------------------
	$routes->get('Transmission_Directeur_Comptable_List','Transmission_Directeur_Comptable_List::index');
	$routes->post('Transmission_Directeur_Comptable_List/listing','Transmission_Directeur_Comptable_List::listing');
	$routes->get('Liste_transmission_bordereau_brb','Liste_transmission_bordereau_brb::index');
	$routes->post('Liste_transmission_bordereau_brb/listing','Liste_transmission_bordereau_brb::listing');
	$routes->get('Liste_Trans_PC', 'Liste_Trans_PC::index');
	$routes->post('Liste_Trans_PC/listing', 'Liste_Trans_PC::listing');
	$routes->get('Reception_BRB/liste_vue','Reception_BRB::liste_vue');
	$routes->post('Reception_BRB/liste_reception_brb','Reception_BRB::liste_reception_brb');
	$routes->post('Reception_BRB/liste_titre_decaissement','Reception_BRB::liste_titre_decaissement');
	$routes->post('Reception_BRB/get_path_bord/(:any)','Reception_BRB::get_path_bord/$1');
	$routes->get('Transmission_Deja_Reception_BRB/liste_trans_rec_vue','Transmission_Deja_Reception_BRB::liste_trans_rec_vue');
	$routes->post('Transmission_Deja_Reception_BRB/liste_transm_deja_recept_brb','Transmission_Deja_Reception_BRB::liste_transm_deja_recept_brb');
	$routes->post('Transmission_Deja_Reception_BRB/liste_reception_decaissement','Transmission_Deja_Reception_BRB::liste_reception_decaissement');
	$routes->post('Transmission_Deja_Reception_BRB/get_path_bordereau/(:any)','Transmission_Deja_Reception_BRB::get_path_bordereau/$1');
	// FIN TRANSMISSION ET RECEPTION
	$routes->get('Liquidation/get_liquidation_rejeter','Liquidation::get_liquidation_rejeter');
	$routes->post('Liquidation/listing_liquidation_rejeter', 'Liquidation::listing_liquidation_rejeter');
	
		//------------- detail double commande----------------------
	$routes->get('detail/(:any)','Liste_activite::detail_view/$1');
  	//--------------- Fin detail -------------------------------
	$routes->get('Liste_Decaissement/detail/(:any)','Liste_Decaissement::detail/$1');

	//---------------debut Liste engagement juridique annulés ---------------------
	$routes->add('Menu_Engagement_Juridique/eng_jur_rejeter','Menu_Engagement_Juridique::eng_jur_rejeter');
	$routes->post('Menu_Engagement_Juridique/listing_jur_rejeter','Menu_Engagement_Juridique::listing_jur_rejeter');
  	//---------------Fin Liste engagement juridique annulés --------------

	$routes->get('Phase_Administrative_Budget/etape_suivi_evaluation/(:any)', 'Phase_Administrative_Budget::etape_suivi_evaluation/$1');
	$routes->post('Phase_Administrative_Budget/save_etape_suivie_evaluation','Phase_Administrative_Budget::save_etape_suivie_evaluation');

	$routes->get('Reception_First_Bord_Transmission/reception','Reception_First_Bord_Transmission::reception');
	$routes->post('Reception_First_Bord_Transmission/save','Reception_First_Bord_Transmission::save');

	$routes->add('Menu_Engagement_Budgetaire/rejete_interface','Menu_Engagement_Budgetaire::rejete_interface');
	$routes->post('Menu_Engagement_Budgetaire/listing_eng_rejette','Menu_Engagement_Budgetaire::listing_eng_rejette');
	$routes->match(['get', 'post'], 'Liste_Decaissement_Deja_Fait', 'Liste_Decaissement_Deja_Fait::index');
	$routes->post('Liste_Decaissement_Deja_Fait/listing','Liste_Decaissement_Deja_Fait::listing');
	$routes->get('Liste_Decaissement_Deja_Fait/get_sous_titre/(:any)','Liste_Decaissement_Deja_Fait::get_sous_titre/$1');

	//--------------- Debut Retour a la correction d'etablissemnt du decaissement --------------------------
	$routes->get('Phase_comptable_prise_en_charge_comptable_Correction/(:any)', 'Phase_comptable_prise_en_charge_comptable_Correction::Retour_Correction/$1');
	$routes->post('Phase_comptable_prise_en_charge_comptable_Correction/Rempli_Donnee', 'Phase_comptable_prise_en_charge_comptable_Correction::Rempli_Donnee');
	$routes->post('Phase_comptable_prise_en_charge_comptable_Correction/Enregitre_Correction', 'Phase_comptable_prise_en_charge_comptable_Correction::Enregitre_Correction');
	//--------------- Fin Retour a la correction d'etablissemnt du decaissement  --------------------------

	$routes->add('Menu_Engagement_Budgetaire/index_A_Valid_SE','Menu_Engagement_Budgetaire::index_A_Valid_SE');
	$routes->post('Menu_Engagement_Budgetaire/listing_a_valide_SE','Menu_Engagement_Budgetaire::listing_a_valide_SE');

	$routes->add('Menu_Engagement_Budgetaire/index_Deja_Valide_SE','Menu_Engagement_Budgetaire::index_Deja_Valide_SE');
	$routes->post('Menu_Engagement_Budgetaire/listing_Deja_Valide_SE','Menu_Engagement_Budgetaire::listing_Deja_Valide_SE');

	$routes->post('Reception_borderau_brb/searchEngagement2','Reception_borderau_brb::searchEngagement2');
	// --------- Start Performance des enqueteur -------------------------
	$routes->get('Dashboard_Performance_Enqueteur', 'Dashboard_Performance_Enqueteur::index');
	$routes->get('Dashboard_Performance_Enqueteur/get_top_enqueteurs_engagements', 'Dashboard_Performance_Enqueteur::get_rapport');
	$routes->post('Dashboard_Performance_Enqueteur/detail_top_performance', 'Dashboard_Performance_Enqueteur::detail_top_performances');
	$routes->post('Dashboard_Performance_Enqueteur/liste_acteurs_performance_vote', 'Dashboard_Performance_Enqueteur::liste_acteurs_performance_votes');
  	// --------- End Performance des enqueteur -------------------------
	$routes->get('Phase_Administrative/vue_ordonnance_ministre/(:any)','Phase_Administrative::vue_ordonnance_ministre/$1');
	$routes->post('Phase_Administrative/update_etape9','Phase_Administrative::update_etape9');
	$routes->get('Phase_Administrative/vue_ordonnance_dgbudget/(:any)','Phase_Administrative::vue_ordonnance_dgbudget/$1');
	$routes->post('Phase_Administrative/update_etapeDG','Phase_Administrative::update_etapeDG');
	//---------------- Debut Correction du projet --------------------------
	$routes->get('Eng_Jurid_Correction_Projet/get_beneficiaire/(:any)','Eng_Jurid_Correction_Projet::get_beneficiaire/$1');
	$routes->get('Eng_Jurid_Correction_Projet/(:any)','Eng_Jurid_Correction_Projet::index/$1');
	$routes->post('Eng_Jurid_Correction_Projet/save','Eng_Jurid_Correction_Projet::save');
	$routes->get('Eng_Jurid_Correction_Projet/','Eng_Jurid_Correction_Projet::index');
  	//---------------- Fin Correction du projet ---------------------------
	//etat d'avancement
	$routes->get('Etat_avancement','Etat_avancement::index');
	$routes->get('Etat_avancement/get_sous_titre/(:any)','Etat_avancement::get_sous_titre/$1');
	$routes->post('Etat_avancement/listing','Etat_avancement::listing');
	//fin etat avancement
	

	//--------------- Debut Liquidation_Double_Commande  --------------------------
	$routes->get('Liquidation/getOne_partiel/(:any)','Liquidation::getOne_partiel/$1');
	$routes->get('Liquidation/getOne_conf/(:any)','Liquidation::getOne_conf/$1');
	$routes->get('Liquidation/getOne_corriger/(:any)','Liquidation::getOne_corriger/$1');
	$routes->get('Liquidation/update_update','Liquidation::update_update');
	$routes->get('Liquidation/createTable', 'Liquidation::createTable');
	$routes->get('Liquidation_Double_Commande/get_liquid_Afaire','Liquidation_Double_Commande::get_liquid_Afaire');
	$routes->post('Liquidation_Double_Commande/listing_liquid_Afaire','Liquidation_Double_Commande::listing_liquid_Afaire');
	$routes->get('Liquidation_Double_Commande/get_liquid_deja_fait','Liquidation_Double_Commande::get_liquid_deja_fait');
	$routes->post('Liquidation_Double_Commande/listing_liquid_deja_fait', 'Liquidation_Double_Commande::listing_liquid_deja_fait');
	$routes->get('Liquidation_Double_Commande/get_liquid_Avalider','Liquidation_Double_Commande::get_liquid_Avalider');
	$routes->post('Liquidation_Double_Commande/listing_liquid_Avalider', 'Liquidation_Double_Commande::listing_liquid_Avalider');
	$routes->get('Liquidation_Double_Commande/get_liquid_Acorriger','Liquidation_Double_Commande::get_liquid_Acorriger');
	$routes->post('Liquidation_Double_Commande/listing_liquid_Acorriger', 'Liquidation_Double_Commande::listing_liquid_Acorriger');
	$routes->get('Liquidation_Double_Commande/get_liquid_valider','Liquidation_Double_Commande::get_liquid_valider');
	$routes->post('Liquidation_Double_Commande/listing_liquid_valider', 'Liquidation_Double_Commande::listing_liquid_valider');
	$routes->match(['get', 'post'], 'Liquidation_Double_Commande/getSousTutel', 'Liquidation_Double_Commande::getSousTutel');

	//--------------- Fin Liquidation_Double_Commande  --------------------------

	//--------------- Debut Ordonnancement_Double_Commande  --------------------------
	$routes->get('Ordonnancement_Double_Commande/get_ordon_Afaire','Ordonnancement_Double_Commande::get_ordon_Afaire');
	$routes->post('Ordonnancement_Double_Commande/listing_ordon_Afaire', 'Ordonnancement_Double_Commande::listing_ordon_Afaire');
	$routes->get('Ordonnancement_Double_Commande/get_ordon_deja_fait','Ordonnancement_Double_Commande::get_ordon_deja_fait');
	$routes->post('Ordonnancement_Double_Commande/listing_ordon_deja_fait', 'Ordonnancement_Double_Commande::listing_ordon_deja_fait');
	//--------------- Fin Ordonnancement_Double_Commande  --------------------------

	$routes->get('Eng_Jurid_Preparation_Projet/get_beneficiaire/(:any)','Eng_Jurid_Preparation_Projet::get_beneficiaire/$1');
	$routes->get('Liste_Paiement','Liste_Paiement::index');
	$routes->post('Liste_Paiement/listing','Liste_Paiement::listing');
	$routes->get('Liste_Paiement_Deja_Fait','Liste_Paiement_Deja_Fait::index');
	$routes->post('Liste_Paiement_Deja_Fait/listing','Liste_Paiement_Deja_Fait::listing');
	$routes->get('Liste_Paiement/get_sous_titre/(:any)','Liste_Paiement::get_sous_titre/$1');
	$routes->get('Liste_Paiement_Deja_Fait/get_sous_titre/(:any)','Liste_Paiement_Deja_Fait::get_sous_titre/$1');
	$routes->get('Liste_Decaissement','Liste_Decaissement::index');
	$routes->post('Liste_Decaissement/listing','Liste_Decaissement::listing');
	$routes->get('Liste_Decaissement/get_sous_titre/(:any)','Liste_Decaissement::get_sous_titre/$1');

	
	################################## Le temps que prend un mouvement #####################
	$routes->get('Duree_Etapes/compter_duree','Duree_Etapes::compter_duree');
	################################## Debut Transfert Meme Activite  #####################
	$routes->get('Transfert_Meme_Activite','Transfert_Meme_Activite::index');
	$routes->get('Transfert_Meme_Activite/add', 'Transfert_Meme_Activite::add');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/getSousTutel', 'Transfert_Meme_Activite::getSousTutel');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/get_code', 'Transfert_Meme_Activite::get_code');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/get_activitesByCode', 'Transfert_Meme_Activite::get_activitesByCode');
	$routes->match(['get','post'],'Transfert_Meme_Activite/getMontantAnnuel','Transfert_Meme_Activite::getMontantAnnuel');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/get_MontantVoteByActivite', 'Transfert_Meme_Activite::get_MontantVoteByActivite');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/getInfoDetail', 'Transfert_Meme_Activite::getInfoDetail');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/send_data', 'Transfert_Meme_Activite::send_data');
	$routes->match(['get', 'post'], 'Transfert_Meme_Activite/deleteFile', 'Transfert_Meme_Activite::deleteFile');
	################################## Fin Transfert Meme Activite #####################
	//preparation du projet
	$routes->get('Eng_Jurid_Preparation_Projet/add/(:any)','Eng_Jurid_Preparation_Projet::add/$1');
	$routes->post('Eng_Jurid_Preparation_Projet/save','Eng_Jurid_Preparation_Projet::save');
	//fin preparation du projet
	$routes->get('Reception_Contrat/add/(:any)','Reception_Contrat::add/$1');
	$routes->post('Reception_Contrat/save','Reception_Contrat::save');
	//etablissement de la facture
	$routes->get('Etablissement_Facture/add/(:any)','Etablissement_Facture::add/$1');
	$routes->post('Etablissement_Facture/save','Etablissement_Facture::save');

	//validation des écritures comptables
	$routes->get('Validation_Ecriture_Comptable/add/(:any)','Validation_Ecriture_Comptable::add/$1');
	$routes->post('Validation_Ecriture_Comptable/save','Validation_Ecriture_Comptable::save');

	//validation des écritures comptables
	$routes->get('Validation_Ecriture_Comptable/transfert_ecriture/(:any)','Validation_Ecriture_Comptable::transfert_ecriture/$1');
	$routes->post('Validation_Ecriture_Comptable/save_transfert','Validation_Ecriture_Comptable::save_transfert');
	//rapprochement des écritures
	$routes->get('Validation_Ecriture_Comptable/rapprochement/(:any)','Validation_Ecriture_Comptable::rapprochement/$1');
	$routes->post('Validation_Ecriture_Comptable/save_rapprochement','Validation_Ecriture_Comptable::save_rapprochement');
	//fin rapprochement des écritures

	// Routes pour la reception du bordereau par brb 

	$routes->get('Reception_borderau_brb/list_bordereau_transmission/(:any)','Reception_borderau_brb::list_bordereau_transmission/$1');
	$routes->post('Reception_borderau_brb/searchEngagement','Reception_borderau_brb::searchEngagement');
	$routes->post("Phase_comptable/search_engagement", "Phase_comptable::search_engagement");
	$routes->post('Reception_borderau_brb/insertion_histo','Reception_borderau_brb::insertion_histo');

	///Route pour transmission du bordereau brb
	$routes->get('Transmission_borderau_brb/(:any)','Transmission_borderau_brb::index/$1');
	$routes->post('Transmission_borderau_brb/add','Transmission_borderau_brb::add');
	// fin 

	$routes->match(['get', 'post'], 'Transmission_borderau_brb/getInfoDetail','Transmission_borderau_brb::getInfoDetail');
	$routes->match(['get', 'post'], 'Transmission_borderau_brb/deleteFile', 'Transmission_borderau_brb::deleteFile');

	$routes->get('Phase_comptable_prise_en_charge_comptable_Correction/(:any)', 'Phase_comptable_prise_en_charge_comptable_Correction::Retour_Correction/$1');
	$routes->post('Phase_comptable_prise_en_charge_comptable_Correction/Rempli_Donnee', 'Phase_comptable_prise_en_charge_comptable_Correction::Rempli_Donnee');
	$routes->post('Phase_comptable_prise_en_charge_comptable_Correction/Enregitre_Correction', 'Phase_comptable_prise_en_charge_comptable_Correction::Enregitre_Correction');

	$routes->get('Phase_Comptable_Directeur_Comptable/formulaire', 'Phase_Comptable_Directeur_Comptable::formulaire');
	$routes->post('Phase_Comptable_Directeur_Comptable/save','Phase_Comptable_Directeur_Comptable::save');
	
	//
	///reception du bordereau par le service de prise en charge
	$routes->get("Borderaux_de_transmission/index/(:any)", "Borderaux_de_transmission::index/$1");
	$routes->post("Borderaux_de_transmission/search", "Borderaux_de_transmission::search");
	$routes->post("Borderaux_de_transmission/search_engagement", "Borderaux_de_transmission::search_engagement");
	$routes->post("Borderaux_de_transmission/store", "Borderaux_de_transmission::store");

	//Approbation du contrat
	$routes->get('Phase_Administrative/approb_contrat/(:any)','Phase_Administrative::approb_contrat/$1');
	$routes->post('Phase_Administrative/update_approb_contr','Phase_Administrative::update_approb_contr');

	//--------------- Debut Transfert_Double_Commande  --------------------------
	$routes->get('Transfert_Double_Commande','Transfert_Double_Commande::index');
	$routes->get('Transfert_Double_Commande/add', 'Transfert_Double_Commande::add');

	$routes->match(['get', 'post'], 'Transfert_Double_Commande/getSousTutel', 'Transfert_Double_Commande::getSousTutel');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/get_code', 'Transfert_Double_Commande::get_code');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/get_activitesByCode', 'Transfert_Double_Commande::get_activitesByCode');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/getMontantAnnuel', 'Transfert_Double_Commande::getMontantAnnuel');

	$routes->match(['get', 'post'],'Transfert_Double_Commande/getSousTutel2','Transfert_Double_Commande::getSousTutel2');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/get_code2', 'Transfert_Double_Commande::get_code2');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/get_activitesByCode2', 'Transfert_Double_Commande::get_activitesByCode2');
	$routes->post('Transfert_Double_Commande/get_MontantVoteByActivite', 'Transfert_Double_Commande::get_MontantVoteByActivite');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/addToCart', 'Transfert_Double_Commande::addToCart');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/removeToCart', 'Transfert_Double_Commande::removeToCart');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/liste_tempo', 'Transfert_Double_Commande::liste_tempo');
	$routes->match(['get', 'post'], 'Transfert_Double_Commande/send_data', 'Transfert_Double_Commande::send_data');
	//--------------- Fin Transfert_Double_Commande  --------------------------

	//---------------Debut Analyse du contrat ----------------------------
	$routes->get('Analyse_Du_Contrat/ajout/(:any)','Analyse_Du_Contrat::ajout/$1');
	$routes->post('Analyse_Du_Contrat/insert','Analyse_Du_Contrat::insert');
  	//---------------Fin Analyse du contrat ------------------------------

	//---------------Debut Analyse du contrat ----------------------------
	$routes->add('Analyse_Du_Contrat/Liste','Analyse_Du_Contrat::index');
	$routes->post('Analyse_Du_Contrat/listing','Analyse_Du_Contrat::listing');
	$routes->get('Analyse_Du_Contrat/ajout/(:any)','Analyse_Du_Contrat::ajout/$1');
	$routes->post('Analyse_Du_Contrat/insert','Analyse_Du_Contrat::insert');
	//---------------Fin Analyse du contrat ------------------------------


	//--------------- debut Liste activite ---------------------
	$routes->get('Liste_activite','Liste_activite::index');
	$routes->post('Liste_activite/listing','Liste_activite::listing');
	$routes->post('Liste_activite/get_soutut','Liste_activite::get_soutut');
	$routes->post('Liste_activite/get_etape','Liste_activite::get_etape');
  	//--------------- Fin Liste activite -------------------------

	//--------------- Debut Transfert  --------------------------
	$routes->get('Transfert','Transfert::index');
	$routes->get('Transfert/add', 'Transfert::add');
	$routes->match(['get', 'post'], 'Transfert/getSousTutel', 'Transfert::getSousTutel');
	$routes->match(['get', 'post'], 'Transfert/get_code', 'Transfert::get_code');
	$routes->match(['get', 'post'], 'Transfert/get_activitesByCode', 'Transfert::get_activitesByCode');
	$routes->match(['get', 'post'], 'Transfert/getMontantAnnuel', 'Transfert::getMontantAnnuel');
	$routes->post('Transfert/getMontantAnnuel', 'Transfert::getMontantAnnuel');
	//--------------- Fin Transfert  --------------------------

	// ---------------------------- DEBUT ENGAGEMENT JURIDIQUE  -----------------------
	$routes->get('Phase_Administrative/vue_list_juridique','Phase_Administrative::vue_list_juridique');
	$routes->post('Phase_Administrative/listing_juridique','Phase_Administrative::listing_juridique');
	$routes->get('Phase_Administrative/eng_juridique/(:any)','Phase_Administrative::eng_juridique/$1');
	$routes->get('Phase_Administrative/corriger_juridique/(:any)','Phase_Administrative::corriger_juridique/$1');
	$routes->get('Phase_Administrative/confirmer_juridique/(:any)','Phase_Administrative::confirmer_juridique/$1');
	$routes->get('Phase_Administrative/reception_juridique/(:any)','Phase_Administrative::reception_juridique/$1');
	$routes->post('Phase_Administrative/update_etape4','Phase_Administrative::update_etape4');
	$routes->post('Phase_Administrative/update_etape5','Phase_Administrative::update_etape5');
	$routes->post('Phase_Administrative/update_etape6','Phase_Administrative::update_etape6');
	$routes->post('Phase_Administrative/update_corriger_etape4','Phase_Administrative::update_corriger_etape4');
	$routes->post('Phase_Administrative/get_benef','Phase_Administrative::get_benef');
	$routes->post('Phase_Administrative/get_soutut','Phase_Administrative::get_soutut');
	// ---------------------------- FIN ENGAGEMENT JURIDIQUE  --------------------------

	// ---------------------------- DEBUT ORDONNANCEMENT ----------------------------
	$routes->get('Phase_Administrative/vue_list_ordonnance','Phase_Administrative::vue_list_ordonnance');
	$routes->post('Phase_Administrative/listing_ordo','Phase_Administrative::listing_ordo');
	$routes->get('Phase_Administrative/vue_ordonnance/(:any)','Phase_Administrative::vue_ordonnance/$1');
	$routes->post('Phase_Administrative/update_etape9','Phase_Administrative::update_etape9');
	$routes->get('Phase_Administrative/approb_contrat/(:any)','Phase_Administrative::approb_contrat/$1');
	$routes->post('Phase_Administrative/update_approb_contr','Phase_Administrative::update_approb_contr');
	// ---------------------------- FIN ORDONNANCEMENT ------------------------------

	//--------------- Debut phase comptable  --------------------------
	$routes->post('Phase_comptable/save_reception_obr', 'Phase_comptable::save_reception_obr');
	$routes->post('Phase_comptable/save_prise_en_charge_comptable', 'Phase_comptable::save_prise_en_charge_comptable');
	$routes->post('Phase_comptable/save_reception_et_signature_titre', 'Phase_comptable::save_reception_et_signature_titre');
	$routes->post('Phase_comptable/save_analyse_depense', 'Phase_comptable::save_analyse_depense');
	$routes->post('Phase_comptable/save_prise_en_charge_etablissement', 'Phase_comptable::save_prise_en_charge_etablissement');
	$routes->get('Phase_comptable/reception/(:any)', 'Phase_comptable::reception/$1');
	$routes->get('Phase_comptable/prise_en_charge/(:any)', 'Phase_comptable::prise_en_charge/$1');
	$routes->get('Phase_comptable/analyse/(:any)', 'Phase_comptable::analyse/$1');
	$routes->get('Phase_comptable/ecriture/(:any)', 'Phase_comptable::ecriture/$1');
	$routes->post('Phase_comptable/save_ecriture', 'Phase_comptable::save_ecriture');
	$routes->post('Phase_comptable/save_analyse', 'Phase_comptable::save_analyse');
	$routes->post('Phase_comptable/save_prise_en_charge', 'Phase_comptable::save_prise_en_charge');
	$routes->post('Phase_comptable/save_reception', 'Phase_comptable::save_reception');
	$routes->post('Phase_comptable/save_analyse_obr', 'Phase_comptable::save_analyse_obr');
	$routes->get('Phase_comptable/analyse_obr/(:any)', 'Phase_comptable::analyse_obr/$1');
	$routes->get('Phase_comptable/etats_brb/(:any)', 'Phase_comptable::etats_brb/$1');
	$routes->post('Phase_comptable/save_etats_brb', 'Phase_comptable::save_etats_brb');
	$routes->get('Phase_comptable/Rec_Analyse_Decaisse_Transmettre/(:any)','Phase_comptable_etape789::index_dec/$1');
	$routes->post('Phase_comptable/Rec_Analyse_Decaisse_Transmettre/save','Phase_comptable_etape789::save_dec');
	$routes->get('Phase_comptable/Rec_Valide_Transmettre/(:any)','Phase_comptable_etape789::index_rec/$1');
	$routes->post('Phase_comptable/Rec_Valide_Transmettre/save','Phase_comptable_etape789::save_rec');
	$routes->get('Phase_comptable/Td_Signe_Ministre/(:any)','Phase_comptable_etape789::index_td/$1');
	$routes->post('Phase_comptable/Td_Signe_Ministre/inserer','Phase_comptable_etape789::insert_td');
	$routes->get('Phase_Comptable_Production/etapes13/(:any)','Phase_Comptable_Production::etapes13/$1');
	$routes->get('Phase_Comptable_Production/etapes14/(:any)','Phase_Comptable_Production::etapes14/$1');
	$routes->get('Phase_Comptable_Production/etapes21/(:any)','Phase_Comptable_Production::etapes21/$1');
	$routes->post('Phase_Comptable_Production/save_etapes13','Phase_Comptable_Production::save_etapes13');
	$routes->post('Phase_Comptable_Production/save_etapes14','Phase_Comptable_Production::save_etapes14');
	$routes->post('Phase_Comptable_Production/save_etapes21','Phase_Comptable_Production::save_etapes21');
	$routes->get('Phase_comptable/reception_analyse_courCompte/(:any)','Phase_comptable::reception_analyse_courCompte/$1');
	$routes->get('Phase_comptable/observation_courcompte_par_ministre/(:any)', 'Phase_comptable::observation_courcompte_par_ministre/$1');
	$routes->get('Phase_comptable/formulez_commentaire_courcompte/(:any)', 'Phase_comptable::formulez_commentaire_courcompte/$1');
	$routes->get('Phase_comptable/transmission_commentaire_courcompte/(:any)', 'Phase_comptable::transmission_commentaire_courcompte/$1');
	$routes->get('Phase_comptable/transmission_observation_final/(:any)', 'Phase_comptable::transmission_observation_final/$1');
	$routes->get('Phase_comptable/reception_analyse_commentaire/(:any)', 'Phase_comptable::reception_analyse_commentaire/$1');
	$routes->get('Phase_comptable/reception_integration_amendes/(:any)', 'Phase_comptable::reception_integration_amendes/$1');
	$routes->get('Phase_comptable/reception_transmission_projet_senat/(:any)', 'Phase_comptable::reception_transmission_projet_senat/$1');
	$routes->get('Phase_comptable/reception_analyse_transmission_assemble/(:any)', 'Phase_comptable::reception_analyse_transmission_assemble/$1');
	$routes->get('Phase_comptable/analyse_amanedements_formuler/(:any)', 'Phase_comptable::analyse_amanedements_formuler/$1');
	$routes->get('Phase_comptable/promulgation/(:any)', 'Phase_comptable::promulgation/$1');
	$routes->get('Phase_comptable/prise_en_charge_etablissement/(:any)', 'Phase_comptable::prise_en_charge_etablissement/$1');
	$routes->get('Phase_comptable/reception_obr/(:any)', 'Phase_comptable::reception_obr/$1');
	$routes->get('Phase_comptable/prise_en_charge_comptable/(:any)', 'Phase_comptable::prise_en_charge_comptable/$1');
	$routes->get('Phase_comptable/reception_et_signature_titre/(:any)', 'Phase_comptable::reception_et_signature_titre/$1');
	$routes->get('Phase_comptable/analyse_depense/(:any)', 'Phase_comptable::analyse_depense/$1');
	//--------------- fin phase comptable  ------------------------------

	//--------------- Debut Engagement budgetaire  -------------------------
	$routes->get('Phase_Administrative_Budget/etape1_prime/(:any)','Phase_Administrative_Budget::etape1_prime/$1');
	$routes->post('Phase_Administrative_Budget/save_etape1_prime','Phase_Administrative_Budget::save_etape1_prime');
	$routes->get('Phase_Administrative_Budget/etape1','Phase_Administrative_Budget::etape1');
	$routes->post('Phase_Administrative_Budget/save_etape1','Phase_Administrative_Budget::save_etape1');
	$routes->get('Phase_Administrative_Budget/get_sousTutel/(:any)','Phase_Administrative_Budget::get_sousTutel/$1');
	$routes->post('Phase_Administrative_Budget/get_code/(:any)', 'Phase_Administrative_Budget::get_code/$1');
	$routes->get('Phase_Administrative_Budget/get_activitesByCode/(:any)','Phase_Administrative_Budget::get_activitesByCode/$1');
	$routes->post('Phase_Administrative_Budget/save_etape1', 'Phase_Administrative_Budget::save_etape1');
	$routes->get('Phase_Administrative_Budget/corrige_etape1/(:any)', 'Phase_Administrative_Budget::corrige_etape1/$1');
	$routes->get('Phase_Administrative_Budget/etape2/(:any)', 'Phase_Administrative_Budget::etape2/$1');
	$routes->post('Phase_Administrative_Budget/activiteGet/(:any)','Phase_Administrative_Budget::activiteGet/$1');
	$routes->post('Phase_Administrative_Budget/liste_bon_budgetaire','Phase_Administrative_Budget::liste_bon_budgetaire');
	$routes->get('Phase_Administrative_Budget/LISTE1','Phase_Administrative_Budget::LISTE1');
	$routes->post('Phase_Administrative_Budget/etape1_correction','Phase_Administrative_Budget::etape1_correction');
	$routes->post('Phase_Administrative_Budget/save_etape2','Phase_Administrative_Budget::save_etape2');
	$routes->get('Phase_Administrative_Budget/etape3/(:any)', 'Phase_Administrative_Budget::etape3/$1');
	$routes->post('Phase_Administrative_Budget/get_activitesMoney/(:any)', 'Phase_Administrative_Budget::get_activitesMoney/$1');
	//--------------- Fin Engagement budgetaire  ----------------------------

	//--------------- Debut Liquidation  --------------------------
	$routes->get('Liquidation','Liquidation::index');
	$routes->post('Liquidation/listing', 'Liquidation::listing');
	$routes->get('Liquidation/getOne/(:any)','Liquidation::getOne/$1');
	$routes->post('Liquidation/add', 'Liquidation::add');
	$routes->post('Liquidation/insert', 'Liquidation::insert');
	$routes->post('Liquidation/update', 'Liquidation::update');
	$routes->match(['get', 'post'], 'Liquidation/getInfoDetail', 'Liquidation::getInfoDetail');
	$routes->match(['get', 'post'], 'Liquidation/getInfoDetail2', 'Liquidation::getInfoDetail2');
	$routes->match(['get', 'post'], 'Liquidation/liste_historique_liquidation', 'Liquidation::liste_historique_liquidation');
	$routes->get('Liquidation/get_liquid_partiel','Liquidation::get_liquid_partiel');
	$routes->match(['get', 'post'], 'Liquidation/listing_liquid_partiel', 'Liquidation::listing_liquid_partiel');
	$routes->match(['get', 'post'], 'Liquidation/getSousTutel', 'Liquidation::getSousTutel');
	$routes->match(['get', 'post'], 'Liquidation/generatePdf', 'Liquidation::generatePdf');
	$routes->match(['get', 'post'], 'Liquidation/deleteFile', 'Liquidation::deleteFile');
	$routes->match(['get', 'post'], 'Liquidation/getNbrJrs', 'Liquidation::getNbrJrs');
	$routes->get('Liquidation:get_liquidation_rejeter','Liquidation::get_liquidation_rejeter');
	$routes->post('Liquidation/listing_liquidation_rejeter', 'Liquidation::listing_liquidation_rejeter');
	$routes->post('Liquidation/add_partiel', 'Liquidation::add_partiel');
	$routes->post('Liquidation/update_partielle', 'Liquidation::update_partielle');
	$routes->post('Liquidation/confirme_partielle', 'Liquidation::confirme_partielle');
	//--------------- Fin Liquidation  --------------------------

	$routes->get('Menu_Engagement_Juridique/get_sousTutel/(:any)','Menu_Engagement_Juridique::get_sousTutel/$1');
  	//---------------debut Liste engagement juridique à faire ---------------------
	$routes->add('Menu_Engagement_Juridique','Menu_Engagement_Juridique::index');
	$routes->post('Menu_Engagement_Juridique/listing','Menu_Engagement_Juridique::listing');
  	//---------------Fin Liste Liste engagement juridique à faire --------------
  	//---------------debut Liste engagement juridique déjà faits ---------------------
	$routes->add('Menu_Engagement_Juridique/eng_jur_deja_fait','Menu_Engagement_Juridique::eng_jur_deja_fait');
	$routes->post('Menu_Engagement_Juridique/listing_deja_fait','Menu_Engagement_Juridique::listing_deja_fait');
  	//---------------Fin Liste engagement juridique déjà faits --------------
  	//---------------debut Liste engagement juridique à valider ---------------------
	$routes->add('Menu_Engagement_Juridique/eng_jur_valider','Menu_Engagement_Juridique::eng_jur_valider');
	$routes->post('Menu_Engagement_Juridique/listing_jur_valider','Menu_Engagement_Juridique::listing_jur_valider');
  	//---------------Fin Liste engagement juridique à valider --------------
  	//---------------debut Liste engagement juridique deéjà validé ---------------------
	$routes->add('Menu_Engagement_Juridique/eng_jur_deja_valide','Menu_Engagement_Juridique::eng_jur_deja_valide');
	$routes->post('Menu_Engagement_Juridique/listing_deja_valide','Menu_Engagement_Juridique::listing_deja_valide');
  	//---------------Fin Liste engagement juridique déjà validé --------------
  	//---------------debut Liste engagement juridique à corriger ---------------------
	$routes->add('Menu_Engagement_Juridique/eng_jur_corriger','Menu_Engagement_Juridique::eng_jur_corriger');
	$routes->post('Menu_Engagement_Juridique/listing_jur_corriger','Menu_Engagement_Juridique::listing_jur_corriger');
  	//---------------Fin Liste engagement juridique à corriger --------------

  	//--------------- debut menu engagement budgetaire ------------------------
	$routes->post('Menu_Engagement_Budgetaire/listing_sans_be','Menu_Engagement_Budgetaire::listing_sans_be');
	$routes->add('Menu_Engagement_Budgetaire/get_sans_bon_engagement','Menu_Engagement_Budgetaire::get_sans_bon_engagement');
	$routes->post('Menu_Engagement_Budgetaire/get_soutut','Menu_Engagement_Budgetaire::get_soutut');
	$routes->add('Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait','Menu_Engagement_Budgetaire::index_deja_fait');
	$routes->post('Menu_Engagement_Budgetaire/listing_Deja_Fait','Menu_Engagement_Budgetaire::listing_Deja_Fait');
	$routes->add('Menu_Engagement_Budgetaire/Eng_Budg_A_Valide','Menu_Engagement_Budgetaire::index_A_Valide');
	$routes->post('Menu_Engagement_Budgetaire/Eng_Budg_A_Valide/listing_A_Valide','Menu_Engagement_Budgetaire::listing_A_Valide');
	$routes->add('Menu_Engagement_Budgetaire/Eng_Budg_A_Faire','Menu_Engagement_Budgetaire::index_a_faire');
	$routes->post('Menu_Engagement_Budgetaire/listing_A_Faire','Menu_Engagement_Budgetaire::listing_A_Faire');
	$routes->add('Menu_Engagement_Budgetaire/Eng_Budg_Corr','Menu_Engagement_Budgetaire::index_A_Corr');
	$routes->post('Menu_Engagement_Budgetaire/Eng_Budg_Corr/listing_A_Corrige','Menu_Engagement_Budgetaire::listing_A_Corrige');
	$routes->add('Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide','Menu_Engagement_Budgetaire::index_Deja_Valide');
	$routes->post('Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide/listing_Deja_Valide','Menu_Engagement_Budgetaire::listing_Deja_Valide');
  	//--------------- Fin menu engagement budgetaire --------------------------

	$routes->post('Phase_Administrative_Budget/get_taux/(:any)', 'Phase_Administrative_Budget::get_taux/$1');

	$routes->post("reception_titre_decaissement/search_engagement", "reception_titre_decaissement::search_engagement");
	$routes->post("reception_titre_decaissement/search_engagement3", "reception_titre_decaissement::search_engagement3");
	$routes->get("reception_titre_decaissement/index/(:any)", "reception_titre_decaissement::index/$1");
	$routes->post("reception_titre_decaissement/store", "reception_titre_decaissement::store");
	//--------------- Debut prestataire -----------------------
	$routes->get('Prestataire','Prestataire::index');
	$routes->post('Prestataire/listing','Prestataire::listing');
	$routes->get('Prestataire/add','Prestataire::add');
	$routes->match(['get', 'post'], 'Prestataire/save', 'Prestataire::save');
	$routes->get('Controles_Decaissement/controle_brb','Controles_Decaissement::controle_brb');
	$routes->post('Controles_Decaissement/controle_brb_listing','Controles_Decaissement::controle_brb_listing');
	$routes->get('Controles_Decaissement/controle_besd','Controles_Decaissement::controle_besd');
	$routes->post('Controles_Decaissement/controle_besd_listing','Controles_Decaissement::controle_besd_listing');
	$routes->post('Controles_Decaissement/detail_task','Controles_Decaissement::detail_task');
	$routes->get('Controles_Decaissement/interface_brb/(:any)','Controles_Decaissement::interface_brb/$1');
	$routes->post('Controles_Decaissement/save_brb','Controles_Decaissement::save_brb');
	$routes->get('Controles_Decaissement/interface_besd/(:any)','Controles_Decaissement::interface_besd/$1');
	$routes->post('Controles_Decaissement/save_besd','Controles_Decaissement::save_besd');
	$routes->get('Controles_Decaissement/exporter_Excel_BESD/(:any)','Controles_Decaissement::exporter_Excel_BESD/$1');
	//--------------- fin prestataire -------------------------//
});
############### fin module double commande new ##################

############### Debut module Transfert new  ##################
$routes->group('transfert_new', ['namespace' => 'App\Modules\transfert_new\Controllers'], function ($routes)
{
	//liste des transferts entre activités
	$routes->get('Liste_Transfert_Entre_Activite', 'Liste_Transfert_Entre_Activite::index'); 
	$routes->post('Liste_Transfert_Entre_Activite/liste', 'Liste_Transfert_Entre_Activite::liste'); 
	$routes->post('Liste_Transfert_Entre_Activite/liste_preuve/(:any)', 'Liste_Transfert_Entre_Activite::liste_preuve/$1'); 
	//fin liste des transferts entre activités
	$routes->get('Transfert_list', 'Transfert_list::index'); 
	$routes->post('Transfert_list/liste', 'Transfert_list::liste');
	$routes->post('Transfert_list/liste_preuve/(:any)', 'Transfert_list::liste_preuve/$1');
	$routes->get('Transfert_list/add_preuve/(:any)', 'Transfert_list::add_preuve/$1');
	$routes->post('Transfert_list/save_preuve', 'Transfert_list::save_preuve');
	############################## Debut correction imputation  #####################
	$routes->get('Correction_Erreur_Imputation','Correction_Erreur_Imputation::index');
	$routes->get('Correction_Erreur_Imputation/add', 'Correction_Erreur_Imputation::add');
	$routes->match(['get', 'post'], 'Correction_Erreur_Imputation/getMontantRecevoirByEtatExecution', 'Correction_Erreur_Imputation::getMontantRecevoirByEtatExecution');
	$routes->match(['get', 'post'], 'Correction_Erreur_Imputation/addtocart', 'Correction_Erreur_Imputation::addToCart');
	$routes->match(['get', 'post'], 'Correction_Erreur_Imputation/removeToCart', 'Correction_Erreur_Imputation::removeToCart');
	$routes->post('Correction_Erreur_Imputation/liste_tempo', 'Correction_Erreur_Imputation::liste_tempo');
	$routes->post('Correction_Erreur_Imputation/getMontantAnnuel', 'Correction_Erreur_Imputation::getMontantAnnuel');
	$routes->post('Correction_Erreur_Imputation/send_data', 'Correction_Erreur_Imputation::send_data');
	################################## Fin correction imputation #####################
	
	$routes->post('Transfert/getMontantAnnuel', 'Transfert::getMontantAnnuel');
	$routes->post('Ptba_contr/get_code', 'Ptba_contr::get_code');
	
	################################## Debut transfert entre deux activité  #####################
	$routes->get('Transfert_Entre_Deux_Activite','Transfert_Entre_Deux_Activite::index');
	$routes->get('Transfert_Entre_Deux_Activite/add', 'Transfert_Entre_Deux_Activite::add');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/get_activitesByCode', 'Transfert_Entre_Deux_Activite::get_activitesByCode');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/get_MontantVoteByActivite', 'Transfert_Entre_Deux_Activite::get_MontantVoteByActivite');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/get_MontantVoteByActivite2', 'Transfert_Entre_Deux_Activite::get_MontantVoteByActivite2');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/addtocart', 'Transfert_Entre_Deux_Activite::addToCart');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/removeToCart', 'Transfert_Entre_Deux_Activite::removeToCart');
	$routes->post('Transfert_Entre_Deux_Activite/liste_tempo', 'Transfert_Entre_Deux_Activite::liste_tempo');
	$routes->post('Transfert_Entre_Deux_Activite/send_data', 'Transfert_Entre_Deux_Activite::send_data');
	################################## Fin transfert entre deux activité #####################

	$routes->get('Transfert_incrim', 'Transfert_incrim::index');
	$routes->post('Transfert_incrim/listing', 'Transfert_incrim::Listing');

	//Debut transfert
	$routes->get('Transfert', 'Transfert::index');
	$routes->post('Transfert/listing', 'Transfert::Listing');
	$routes->get('Transfert/getOne/(:any)','Transfert::getOne/$1');
	$routes->get('Transfert/get_montantss/(:any)', 'Transfert::get_montant/$1');
	$routes->get('Transfert/get_montant_act_transfert/(:any)', 'Transfert::get_montant_act_transfert/$1');
	$routes->post('Transfert/get_code/(:any)', 'Transfert::get_code/$1');
	$routes->post('Transfert/get_activite/(:any)', 'Transfert::get_activite/$1');
	$routes->post('Transfert/get_summ_activite/(:any)', 'Transfert::get_summ_activite/$1');
	$routes->post('Transfert/enregistre_tempo', 'Transfert::enregistre_tempo');
	$routes->post('Transfert/save_transfert', 'Transfert::save_transfert');
	$routes->get('Transfert/deleteData/(:any)','Transfert::deleteData/$1');
	//fin transfert


	###############  Debut Transfert d'Incrementation  ###############
	$routes->get('Transfert_Incrementation','Transfert_Incrementation::index');
	$routes->get('Transfert_Incrementation/getOne/(:any)', 'Transfert_Incrementation::getOne/$1');

	$routes->match(['get', 'post'], 'Transfert_Incrementation/get_code', 'Transfert_Incrementation::get_code');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/get_activitesByCode', 'Transfert_Incrementation::get_activitesByCode');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/get_MontantVoteByActivite', 'Transfert_Incrementation::get_MontantVoteByActivite');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/get_MontantVoteByActivite2', 'Transfert_Incrementation::get_MontantVoteByActivite2');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/addtocart', 'Transfert_Incrementation::addToCart');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/removeToCart', 'Transfert_Incrementation::removeToCart');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/addtocart2', 'Transfert_Incrementation::addtocart2');
	$routes->match(['get', 'post'], 'Transfert_Incrementation/removeToCart2', 'Transfert_Incrementation::removeToCart2');
	$routes->post('Transfert_Incrementation/send_data', 'Transfert_Incrementation::send_data');
	$routes->post('Transfert_Incrementation/liste_tempo', 'Transfert_Incrementation::liste_tempo');
	$routes->post('Transfert_Incrementation/getMontantAnnuel', 'Transfert_Incrementation::getMontantAnnuel');
	###############  Fin Transfert d'Incrementation  ###############


	################################## Debut transfert entre deux activité  #####################
	$routes->get('Transfert_Entre_Deux_Activite','Transfert_Entre_Deux_Activite::index');
	$routes->get('Transfert_Entre_Deux_Activite/add', 'Transfert_Entre_Deux_Activite::add');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/get_activitesByCode', 'Transfert_Entre_Deux_Activite::get_activitesByCode');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/get_MontantVoteByActivite', 'Transfert_Entre_Deux_Activite::get_MontantVoteByActivite');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/get_MontantVoteByActivite2', 'Transfert_Entre_Deux_Activite::get_MontantVoteByActivite2');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/addtocart', 'Transfert_Entre_Deux_Activite::addToCart');
	$routes->match(['get', 'post'], 'Transfert_Entre_Deux_Activite/removeToCart', 'Transfert_Entre_Deux_Activite::removeToCart');
	$routes->post('Transfert_Entre_Deux_Activite/liste_tempo', 'Transfert_Entre_Deux_Activite::liste_tempo');
	$routes->post('Transfert_Entre_Deux_Activite/getMontantAnnuel', 'Transfert_Entre_Deux_Activite::getMontantAnnuel');
	$routes->post('Transfert_Entre_Deux_Activite/send_data', 'Transfert_Entre_Deux_Activite::send_data');
	################################## Fin transfert entre deux activité #####################
});
############### End module Transfert new ##################

############### Debut module pip pour interface affichage seulement ##################
$routes->group('pip', ['namespace' => 'App\Modules\pip\Controllers'], function ($routes)
{
	$routes->get('Source_finance_bailleur','Pip_source_finance_bailleur::index');
	$routes->post('Liste_Source_finance_bailleur','Pip_source_finance_bailleur::Liste_donnees');
	$routes->get('Nouv_Source_finance_bailleur','Pip_source_finance_bailleur::Add_pipFinance');
	$routes->post('Nouvelle_source_finance_bailleur','Pip_source_finance_bailleur::Ajout_donnees');
	$routes->get('get_data/(:any)','Pip_source_finance_bailleur::Recuperation_donnee/$1');
	$routes->post('Modify_data','Pip_source_finance_bailleur::update_data');
	$routes->post('supprimer', 'Pip_source_finance_bailleur::delete');
	$routes->match(['get', 'post'],'Pip_projet_par_ministere_libvrable_projet','Pip_projet_par_ministere_libvrable_projet::index');
	$routes->post('Pip_projet_par_ministere_libvrable_projet/listing_project_livrable','Pip_projet_par_ministere_libvrable_projet::listing_project_livrable');
	$routes->get('Pip_projet_par_ministere_libvrable_projet/exporter','Pip_projet_par_ministere_libvrable_projet::exporter');

	$routes->get('Nomenclature_Pourcentage/add_pourcentage','Nomenclature_Pourcentage::add_pourcentage');
	$routes->post('Nomenclature_Pourcentage/ajouter_pourcentage','Nomenclature_Pourcentage::ajouter_pourcentage');

	$routes->get('Nomenclature_Pourcentage/liste_pourcentage_nomenclature','Nomenclature_Pourcentage::liste_pourcentage_nomenclature');
	$routes->post('Nomenclature_Pourcentage/liste_nomen_pourcent','Nomenclature_Pourcentage::liste_nomen_pourcent');

	$routes->get('Nomenclature_Pourcentage/edit_pourcentage/(:any)','Nomenclature_Pourcentage::edit_pourcentage/$1');
	$routes->post('Nomenclature_Pourcentage/edit_nomen_pourcent','Nomenclature_Pourcentage::edit_nomen_pourcent');

	$routes->post('Nomenclature_Pourcentage/supprimer_pourcentage','Nomenclature_Pourcentage::supprimer_pourcentage');
	$routes->post('Nomenclature_Pourcentage/suppresion/(:any)','Nomenclature_Pourcentage::suppresion/$1');

	$routes->get('Projet_Pip_Fini/liste_pip_fini','Projet_Pip_Fini::liste_pip_fini');
	$routes->match(['get','post'],'Projet_Pip_Fini/annuler_projet_complet/(:any)','Projet_Pip_Fini::annuler_projet_complet/$1');
	$routes->post('Projet_Pip_Fini/liste_projet_fini','Projet_Pip_Fini::liste_projet_fini');
	$routes->get('Projet_Pip_Infini/liste_pip_infini','Projet_Pip_Infini::liste_pip_infini');
	$routes->match(['get','post'],'Projet_Pip_Infini/annuler_projet_incomplet/(:any)','Projet_Pip_Infini::annuler_projet_incomplet/$1');
	$routes->post('Projet_Pip_Infini/liste_projet_infini','Projet_Pip_Infini::liste_projet_infini');
	
	$routes->get('Projet_Pip_Corrige/liste_pip_corrige','Projet_Pip_Corrige::liste_pip_corrige');
	$routes->post('Projet_Pip_Corrige/liste_projet_corrige','Projet_Pip_Corrige::liste_projet_corrige');

	$routes->get('Projet_Pip_Valide/liste_pip_valide','Projet_Pip_Valide::liste_pip_valide');
	$routes->post('Projet_Pip_Valide/liste_projet_valide','Projet_Pip_Valide::liste_projet_valide');

	$routes->get('Projet_Pip_A_Compiler/liste_pip_compiler','Projet_Pip_A_Compiler::liste_pip_compiler');
	$routes->post('Projet_Pip_A_Compiler/liste_projet_compiler','Projet_Pip_A_Compiler::liste_projet_compiler');
	

	$routes->get('Fiche_Pip_Proposer/liste_pip_proposer','Fiche_Pip_Proposer::liste_pip_proposer');
	$routes->post('Fiche_Pip_Proposer/liste_projet_proposer','Fiche_Pip_Proposer::liste_projet_proposer');
	$routes->post('Fiche_Pip_Proposer/projet_propose/(:any)','Fiche_Pip_Proposer::projet_propose/$1');
	$routes->post('Fiche_Pip_Proposer/get_path_pip/(:any)','Fiche_Pip_Proposer::get_path_pip/$1');
	$routes->get('Fiche_Pip_Corriger/liste_pip_corriger','Fiche_Pip_Corriger::liste_pip_corriger');
	$routes->post('Fiche_Pip_Corriger/liste_projet_corriger','Fiche_Pip_Corriger::liste_projet_corriger');
	$routes->post('Fiche_Pip_Corriger/get_corriger_pip/(:any)','Fiche_Pip_Corriger::get_corriger_pip/$1');

	$routes->post('Fiche_Pip_Corriger/projet_corriger/(:any)','Fiche_Pip_Corriger::projet_corriger/$1');
	$routes->get('Fiche_Pip_Valider/liste_pip_Valider','Fiche_Pip_Valider::liste_pip_Valider');
	$routes->post('Fiche_Pip_Valider/liste_projet_valider','Fiche_Pip_Valider::liste_projet_valider');
	$routes->post('Fiche_Pip_Valider/get_valider_pip/(:any)','Fiche_Pip_Valider::get_valider_pip/$1');
	$routes->post('Fiche_Pip_Valider/projet_valider/(:any)','Fiche_Pip_Valider::projet_valider/$1');
	
	////////////////////CRUD des taux d'echange///////////////////
	$routes->get('Taux_Echange','Taux_Echange::index');
	$routes->get('Taux_Echange/new','Taux_Echange::new');
	$routes->get('Taux_Echange/getOne/(:any)','Taux_Echange::getOne/$1');
	$routes->post('Taux_Echange/listing','Taux_Echange::listing');
	$routes->post('Taux_Echange/save','Taux_Echange::save');
	$routes->post('Taux_Echange/update','Taux_Echange::update');
	$routes->post('Taux_Echange/delete','Taux_Echange::delete');

	////////////////////FIN CRUD des taux d'echange////////

	$routes->get('Repartition_projet','Repartition_projet::index');
	$routes->post('Repartition_projet/list_projet','Repartition_projet::list_projet');
	$routes->get('Repartition_pilier','Repartition_pilier::index');
	$routes->post('Repartition_pilier/list_pilier','Repartition_pilier::list_pilier');
	$routes->get('Repartition_objectif','Repartition_objectif::index');
	$routes->post('Repartition_objectif/list_objectif','Repartition_objectif::list_objectif');
	//A refaire
	$routes->get('Repartition_obj_strat_pnd','Repartition_obj_strat_pnd::index');
	$routes->post('Repartition_obj_strat_pnd/list_objectif_pnd','Repartition_obj_strat_pnd::list_objectif_pnd');

	$routes->get('Repartition_intervention_pnd','Repartition_intervention_pnd::index');
	$routes->post('Repartition_intervention_pnd/list_intervention','Repartition_intervention_pnd::list_intervention');
	$routes->get('Gap_financement_pilier','Gap_financement_pilier::index');
	$routes->post('Gap_financement_pilier/list_gap_pilier','Gap_financement_pilier::list_gap_pilier');
	$routes->get('Gap_financement_institution','Gap_financement_institution::index');
	$routes->post('Gap_financement_institution/list_gap_institution','Gap_financement_institution::list_gap_institution');
	$routes->get('Repartition_budget_rub_budgetaire_invEtpers','Repartition_budget_rub_budgetaire_invEtpers::index');
	$routes->post('Repartition_budget_rub_budgetaire_invEtpers/list_budgt_invEtpers','Repartition_budget_rub_budgetaire_invEtpers::list_budgt_invEtpers');
	$routes->get('Repartition_budget_rub_budgetaire_bienEtTransf','Repartition_budget_rub_budgetaire_bienEtTransf::index');
	$routes->post('Repartition_budget_rub_budgetaire_bienEtTransf/list_budgt_bienEtTransf','Repartition_budget_rub_budgetaire_bienEtTransf::list_budgt_bienEtTransf');
	
	$routes->get('Source_investissement_public','Source_investissement_public::index');
	$routes->post('Source_investissement_public/list_investissement','Source_investissement_public::list_investissement');

	$routes->get("presentation_fichier_investisement_public/liste_projet", "Presentation_fichier_public::liste_projet");
	$routes->get("presentation_fichier_investisement_public/projet_detail/(:any)", "Presentation_fichier_public::projet_detail/$1");
	$routes->post("presentation_fichier_investisement_public/liste_projet_detail", "Presentation_fichier_public::liste_projet_detail");
	######################## Exportation Routes ##########################################################################
	$routes->get('Fichier_Pip_Exel/afficher/(:any)','Fichier_Pip_Exel::afficher/$1');
	$routes->get('Fichier_Pip_Exel/action/(:any)','Fichier_Pip_Exel::action/$1');

	// ----------------------- Debut demande --------------------------------
	$routes->get("Processus_Investissement_Public/demande/livrable/(:any)/(:any)", "Processus_Investissement_Public_Demande::livrable/$2/$1");
	$routes->get('','Processus_Investissement_Public::index', ['as' => 'process.index']);
	$routes->post('Processus_Investissement_Public/listing','Processus_Investissement_Public::listing');
	$routes->get('Processus_Investissement_Public/details/(:any)','Processus_Investissement_Public::details/$1');
	$routes->get('Processus_Investissement_Public/correction_compilation/(:any)','Processus_Investissement_Public::correction_compilation/$1');
	$routes->match(['get', 'post'], 'Processus_Investissement_Public/liste_projet_pip_correction/(:any)','Processus_Investissement_Public::liste_projet_pip_correction/$1');	
	$routes->post('Processus_Investissement_Public/get_etapes','Processus_Investissement_Public::get_etapes');
	$routes->post('Processus_Investissement_Public/Proceed', 'Processus_Investissement_Public::proceed');
	$routes->match(['get', 'post'], 'Processus_Investissement_Public/avancement', 'Processus_Investissement_Public::avancement');
	$routes->post('Processus_Investissement_Public/listing_histo_compilation/(:any)', 'Processus_Investissement_Public::listing_histo_compilation/$1');
	$routes->post('Processus_Investissement_Public/lieu_intervention', 'Processus_Investissement_Public::lieu_intervention');
	$routes->get('Processus_Investissement_Public/getcommunes/(:any)', 'Processus_Investissement_Public::getcommunes/$1');
	$routes->match(['get', 'post'],'Processus_Investissement_Public_Demande/get_info_livrable_cmr/(:any)', 'Processus_Investissement_Public_Demande::get_info_livrable_cmr/$1');
	$routes->get('Processus_Investissement_Public/get_doc_reference/(:any)', 'Processus_Investissement_Public::get_doc_reference/$1');
	$routes->get('crm', 'Processus_Investissement_Public_Demande::crm');

	//-------------------debut routes ajouter dimanche le 17/12/2023-------------------- 
	$routes->get("Processus_Investissement_Public/demande", "Processus_Investissement_Public_Demande::index");
	$routes->post("Processus_Investissement_Public/demande/store/step/(:any)", "Processus_Investissement_Public_Demande::storeStep/$1");
	$routes->post("Processus_Investissement_Public/demande/store", "Processus_Investissement_Public_Demande::store");
	$routes->post("Processus_Investissement_Public/demande/updateProjet", "Processus_Investissement_Public_Demande::updateProjet");
	$routes->post("Processus_Investissement_Public/demande/storeProjet", "Processus_Investissement_Public_Demande::storeProjet");
	$routes->post("Processus_Investissement_Public/demande/storeIntervation", "Processus_Investissement_Public_Demande::storeIntervation");
	$routes->post("Processus_Investissement_Public/demande/storeEtudeDocument", "Processus_Investissement_Public_Demande::storeEtudeDocument");
	$routes->post("Processus_Investissement_Public/demande/storeCmr/(:any)", "Processus_Investissement_Public_Demande::storeCmr/$1");
	$routes->post("Processus_Investissement_Public/demande/storeBpl", "Processus_Investissement_Public_Demande::storeBpl");
	$routes->post("Processus_Investissement_Public/demande/storeRisque/(:any)", "Processus_Investissement_Public_Demande::storeRisque/$1");
	$routes->post("Processus_Investissement_Public/demande/storeObjectif", "Processus_Investissement_Public_Demande::storeObjectif");
	$routes->post("Processus_Investissement_Public/demande/storeLivrable","Processus_Investissement_Public_Demande::storeLivrable");
	$routes->post("Processus_Investissement_Public/demande/storeSFP", "Processus_Investissement_Public_Demande::storeSFP");
	$routes->get("Processus_Investissement_Public/demande/filter/(:any)", "Processus_Investissement_Public_Demande::filtre/$1");
	$routes->get("Processus_Investissement_Public/demande/filtre/commune/(:any)", "Processus_Investissement_Public_Demande::filtre_commune/$1");
	$routes->get("Processus_Investissement_Public/demande/filtre/programme/(:any)","Processus_Investissement_Public_Demande::filtre_programme/$1");
	$routes->get("Processus_Investissement_Public/demande/update/(:any)", "Processus_Investissement_Public_Demande::update/$1");
	$routes->post("Processus_Investissement_Public/demande/deleteInfo", "Processus_Investissement_Public_Demande::deleteInfo");
	$routes->post("Processus_Investissement_Public/demande/updateInfo", "Processus_Investissement_Public_Demande::updateInfo");
	$routes->post("Processus_Investissement_Public/demande/delete/(:any)/(:any)", "Processus_Investissement_Public_Demande::delete/$1/$2");
	$routes->get("Processus_Investissement_Public/demande/objectif_specifique/(:any)", "Processus_Investissement_Public_Demande::objectif_specifique/$1");
	$routes->get("Processus_Investissement_Public/demande/cout_livrable/(:any)", "Processus_Investissement_Public_Demande::cout_livrable/$1");
	$routes->get("Processus_Investissement_Public/demande/cout_nomenclature/(:any)","Processus_Investissement_Public_Demande::cout_nomenclature/$1");
	$routes->get("Processus_Investissement_Public/demande/pays","Processus_Investissement_Public_Demande::pays");
	$routes->get("Processus_Investissement_Public/demande/cmr_livrable/(:any)","Processus_Investissement_Public_Demande::cmr_livrable/$1");
	//-----------------------------------------fin---------------------------------------------
	$routes->get("Processus_Investissement_Public/compilation_projet", "Processus_Investissement_Public::compilation_projet");
	$routes->get("Processus_Investissement_Public/fiches_compiler", "Processus_Investissement_Public::fiches_compiler");
	$routes->post("Processus_Investissement_Public/listing_projet_sur_fiches_compil/(:any)", "Processus_Investissement_Public::listing_projet_sur_fiches_compil/$1");
	$routes->get("Processus_Investissement_Public_Demande/det_projet", "Processus_Investissement_Public_Demande::det_projet");
	$routes->post("Processus_Investissement_Public_Demande/listing_unfished_project", "Processus_Investissement_Public_Demande::listing_unfished_project");
	$routes->get("Processus_Investissement_Public/detail_compiler/(:any)", "Processus_Investissement_Public::detail_compiler/$1");
	$routes->post("Processus_Investissement_Public/liste_projet_acompiler", "Processus_Investissement_Public::liste_projet_acompiler");
	$routes->post("Processus_Investissement_Public/listing_complilation", "Processus_Investissement_Public::listing_complilation");
	$routes->post("Processus_Investissement_Public/save_doc_compilation", "Processus_Investissement_Public::save_doc_compilation");
	$routes->post("Processus_Investissement_Public/save_correction_pip", "Processus_Investissement_Public::save_correction_pip");
	$routes->match(['get','post'],"Processus_Investissement_Public/enlever_projet/(:any)", "Processus_Investissement_Public::enlever_projet/$1");
	// ----------------------- Debut demande -------------------------------
});
############### End module  pip pour interface affichage seulement ##################

############### Debut module process ##################
$routes->group('process', ['namespace' => 'App\Modules\process\Controllers'], function ($routes)
{
	$routes->match(['get','post'],"Liste_Proc_Tache", "Liste_Proc_Tache::index");
	$routes->match(['get','post'],"Liste_Proc_Tache/exporter", "Liste_Proc_Tache::exporter");
	$routes->match(['get','post'],"Liste_Proc_Tache/liste_ptba_prog_budg","Liste_Proc_Tache::liste_ptba_prog_budg");
	$routes->match(['get','post'],"Liste_Proc_Tache/save_tache_ptba_prog","Liste_Proc_Tache::save_tache_ptba_prog");
	$routes->match(['get','post'],"Proc_Tache/save_tache_valider","Proc_Tache::save_tache_valider");
	$routes->post('Proc_Tache/listing_data', 'Proc_Tache::listing_data');

	$routes->match(['get','post'],"Proc_Tache/create/(:any)", "Proc_Tache::create/$1");
	$routes->match(['get','post'],"Proc_Tache/delete", "Proc_Tache::delete");
	$routes ->post('Proc_Tache/get_sous_titre','Proc_Tache::get_sous_titre');
	$routes ->post('Proc_Tache/get_article','Proc_Tache::get_article');
	$routes ->post('Proc_Tache/get_paragraphe','Proc_Tache::get_paragraphe');
	$routes ->post('Proc_Tache/get_littera','Proc_Tache::get_littera');
	$routes ->post('Proc_Tache/get_sous_littera','Proc_Tache::get_sous_littera');
	$routes ->post('Proc_Tache/get_groupe','Proc_Tache::get_groupe');
	$routes ->post('Proc_Tache/get_classe','Proc_Tache::get_classe');
	$routes ->post('Proc_Tache/get_programme','Proc_Tache::get_programme');
	$routes ->post('Proc_Tache/get_action','Proc_Tache::get_action');
	$routes ->post('Proc_Tache/get_code_budgetaire','Proc_Tache::get_code_budgetaire');
	$routes ->post('Proc_Tache/get_pap_activite','Proc_Tache::get_pap_activite');
	$routes ->post('Proc_Tache/save_tache','Proc_Tache::save_tache');
	$routes ->post('Proc_Tache/get_institution_info','Proc_Tache::get_institution_info');
	
	//------------------------------ liste de demande - CDMT - CBMT---------------------
	$routes->get('Demandes_CDMT_CBMT','Demandes_CDMT_CBMT::index');
	$routes->post('Demandes_CDMT_CBMT/listing','Demandes_CDMT_CBMT::listing');
	$routes->post('Demandes_CDMT_CBMT/get_etapes','Demandes_CDMT_CBMT::get_etapes');
    // ---------------------------- liste de demande - CDMT - CBMT-----------------------------
	
	$routes->match(['get','post'],"Dem_Liste_Activites/get_code_int/(:any)", "Dem_Liste_Activites::get_code_int/$1");
	$routes->match(['get','post'],"Liste_Proc_Tache", "Liste_Proc_Tache::index");
	$routes->match(['get','post'],"Liste_Proc_Tache/exporter", "Liste_Proc_Tache::exporter");
	$routes->match(['get','post'],"Liste_Proc_Tache/liste_ptba_prog_budg","Liste_Proc_Tache::liste_ptba_prog_budg");
	$routes->match(['get','post'],"Liste_Proc_Tache/save_tache_ptba_prog","Liste_Proc_Tache::save_tache_ptba_prog");
	$routes->match(['get','post'],"Proc_Tache/save_tache_valider","Proc_Tache::save_tache_valider");
$routes->post('Proc_Tache/listing_data', 'Proc_Tache::listing_data');
$routes->match(['get','post'],"Proc_Tache/create/(:any)", "Proc_Tache::create/$1");
$routes->match(['get','post'],"Proc_Tache/delete", "Proc_Tache::delete");
$routes ->post('Proc_Tache/get_sous_titre','Proc_Tache::get_sous_titre');
$routes ->post('Proc_Tache/get_article','Proc_Tache::get_article');
$routes ->post('Proc_Tache/get_paragraphe','Proc_Tache::get_paragraphe');
$routes ->post('Proc_Tache/get_littera','Proc_Tache::get_littera');
$routes ->post('Proc_Tache/get_sous_littera','Proc_Tache::get_sous_littera');
$routes ->post('Proc_Tache/get_groupe','Proc_Tache::get_groupe');
$routes ->post('Proc_Tache/get_classe','Proc_Tache::get_classe');
$routes ->post('Proc_Tache/get_programme','Proc_Tache::get_programme');
$routes ->post('Proc_Tache/get_action','Proc_Tache::get_action');
$routes ->post('Proc_Tache/get_code_budgetaire','Proc_Tache::get_code_budgetaire');
$routes ->post('Proc_Tache/get_pap_activite','Proc_Tache::get_pap_activite');
$routes ->post('Proc_Tache/save_tache','Proc_Tache::save_tache');
$routes ->post('Proc_Tache/get_institution_info','Proc_Tache::get_institution_info');

$routes->get('Dem_Liste_Activites/activite_demande', 'Dem_Liste_Activites::activite_demande_index');
$routes->post('Dem_Liste_Activites/activites', 'Dem_Liste_Activites::Liste_activite_demandes');

	$routes->post('Demandes_Programmation_Budgetaire/listing_demandes_cl_cmr','Demandes_Programmation_Budgetaire::dem_grogramm_Budg');

	$routes->post('Listing_demandes_costab_programmation','Demandes_Programmation_Budgetaire::programmation_costab');

	//------------------------------ liste de demande ---------------------
	$routes->get('Demandes_Plannification','Demandes_Plannification::index');
	$routes->post('Demandes_Plannification/listing','Demandes_Plannification::listing');
	$routes->post('Demandes_Plannification/get_etapes','Demandes_Plannification::get_etapes');

	$routes->get('Demandes_Program_Budget','Demandes_Program_Budget::index');
	$routes->post('Demandes_Program_Budget/listing','Demandes_Program_Budget::listing');
	$routes->post('Demandes_Program_Budget/get_etapes','Demandes_Program_Budget::get_etapes');
    // ---------------------------- liste de demande -----------------------------

	$routes->get("Planification_Strategique_Sectorielle/getIndicateur/(:any)", "Planification_Strategique_Sectorielle::getIndicateur/$1");
	$routes->get("Planification_demande_cl_cmr_costab/getIndicateur/(:any)", "Planification_demande_cl_cmr_costab::getIndicateur/$1");

	$routes->post('Liste_Activites', 'Dem_Liste_Activites::listing_data');
	$routes->post('supprimer', 'Dem_Liste_Activites::delete');
	$routes->get('Dem_Liste_Activites/update/(:any)', 'Dem_Liste_Activites::Get_one_data/$1');
	$routes->post('modifier_activite', 'Dem_Liste_Activites::update_data');

	$routes->get('confirmations', 'Dem_Liste_Activites::save_confirm');
	$routes->post('tempo_save', 'Dem_Liste_Activites::insert_data');
	
	$routes->get('Processus_Investissement_Public/getcommunes/(:any)/(:any)', 'Processus_Investissement_Public::getcommunes/$1/$2');
	$routes->post("Processus_Investissement_Public/demande/lieuIntervention","Processus_Investissement_Public_Demande::lieuIntervention");
	//...........Debut generation des decuments.......//
	$routes->get('Demandes_Programmation_Budgetaire/generer/(:any)', 'Demandes_Programmation_Budgetaire::generer/$1');
	$routes->match(['get', 'post'], 'Gerate_pdf/htmlToPDF', 'Gerate_pdf::htmlToPDF');
	//...........Fin generation des decuments.......//

	$routes->post('Dem_Liste_Activites/insert', 'Dem_Liste_Activites::insert');
	$routes->get('Dem_Liste_Activites/create/(:any)', 'Dem_Liste_Activites::create/$1');
	$routes->get('Process_planification_Cdmt_Cbmt/detail_cdmt/(:any)', 'Process_planification_Cdmt_Cbmt::detail_cdmt/$1');
	$routes->post('Process_planification_Cdmt_Cbmt/getAction', 'Process_planification_Cdmt_Cbmt::getAction');
	$routes->post('Process_planification_Cdmt_Cbmt/envoyer', 'Process_planification_Cdmt_Cbmt::envoyer');
	$routes->post('Process_planification_Cdmt_Cbmt/historique', 'Process_planification_Cdmt_Cbmt::historique');
	$routes->post('Process_planification_Cdmt_Cbmt/cl_cmr', 'Process_planification_Cdmt_Cbmt::cl_cmr');
	$routes->post('Process_planification_Cdmt_Cbmt/costab', 'Process_planification_Cdmt_Cbmt::costab');
	$routes->post('Process_planification_Cdmt_Cbmt/envoyernote', 'Process_planification_Cdmt_Cbmt::envoyernote');
	###############  Debut Programmation Budgetaire(Demandes_Programmation_Budgetaire)  ###############
	$routes->get('Demandes_Programmation_Budgetaire','Demandes_Programmation_Budgetaire::index');
	$routes->post('Demandes_Programmation_Budgetaire/listing','Demandes_Programmation_Budgetaire::listing');
	$routes->get('Demandes_Programmation_Budgetaire/Details/(:any)','Demandes_Programmation_Budgetaire::details_view/$1');
	$routes->post('Demandes_Programmation_Budgetaire/send_data','Demandes_Programmation_Budgetaire::send_data');
	$routes->post('Demandes_Programmation_Budgetaire/get_etapes','Demandes_Programmation_Budgetaire::get_etapes');
	$routes->post('Demandes_Programmation_Budgetaire/get_infos_docs/(:any)','Demandes_Programmation_Budgetaire::get_infos_docs/$1');
	$routes->post('Demandes_Programmation_Budgetaire/listing_demandes_historique','Demandes_Programmation_Budgetaire::listing_demandes_historique');
	$routes->post('Dem_Liste_Activites/get_programs', 'Dem_Liste_Activites::get_programs');
	############### Fin Programmation Budgetaire(Demandes_Programmation_Budgetaire)###############

	//Debut activités
	$routes->get('Dem_Liste_Activites', 'Dem_Liste_Activites::index');
	$routes->post('Dem_Liste_Activites/listing', 'Dem_Liste_Activites::listing');
	$routes->post('Dem_Liste_Activites/get_prog', 'Dem_Liste_Activites::get_prog');
	$routes->post('Dem_Liste_Activites/get_action', 'Dem_Liste_Activites::get_action');
	$routes->post('Dem_Liste_Activites/create_get_code', 'Dem_Liste_Activites::create_get_code');
	$routes->post('Dem_Liste_Activites/create_get_action', 'Dem_Liste_Activites::create_get_action');
	$routes->post('Dem_Liste_Activites/get_groupes', 'Dem_Liste_Activites::get_groupes');
	$routes->post('Dem_Liste_Activites/get_classes', 'Dem_Liste_Activites::get_classes');
	//Fin activités

	###############  Debut Etat d'avancement  ###############
	$routes->get('Etat_avancement','Etat_Avancement::etat_avancement');
	$routes->post('Etat_avancement/etat_avancement_listing','Etat_Avancement::etat_avancement_listing');
	$routes->post('Etat_avancement/get_profil_etape','Etat_Avancement::get_profil_etape');
	$routes->get('Etat_avancement/get_profils/(:any)','Etat_Avancement::get_profils/$1');
	###############  Debut Etat d'avancement  ###############

	//------------------------------ liste de demande ---------------------
	$routes->get('Demandes','Demandes::index');
	$routes->post('Demandes/listing','Demandes::listing');
	$routes->post('Demandes/get_etapes','Demandes::get_etapes');
    // ---------------------------- liste de demande -----------------------------

	######################################################################################################
	// Debut formulaire cl, cmr et costab
	$routes->get('Planification_demande_cl_cmr_costab/index/(:any)','Planification_demande_cl_cmr_costab::index/$1');

	$routes->get("Planification_demande_cl_cmr_costab/getObjectif/(:any)", "Planification_demande_cl_cmr_costab::getObjectif/$1");

	$routes->get("Planification_demande_cl_cmr_costab/getPilier/(:any)", "Planification_demande_cl_cmr_costab::getPilier/$1");

	$routes->get("Planification_demande_cl_cmr_costab/getAxeObjectif/(:any)", "Planification_demande_cl_cmr_costab::getAxeObjectif/$1");

	$routes->get("Planification_demande_cl_cmr_costab/getPlanification/(:any)", "Planification_demande_cl_cmr_costab::getPlanification/$1");

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/liste_cl_cmr', 'Planification_demande_cl_cmr_costab::liste_cl_cmr');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/supprimer_cl_cmr', 'Planification_demande_cl_cmr_costab::supprimer_cl_cmr');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/editercl_cmr', 'Planification_demande_cl_cmr_costab::editercl_cmr');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/save_cl_cmr', 'Planification_demande_cl_cmr_costab::save_cl_cmr');
	// $routes->post('Planification_demande_cl_cmr_costab/save_cl_cmr','Planification_demande_cl_cmr_costab::save_cl_cmr');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/liste_costab/(:any)', 'Planification_demande_cl_cmr_costab::liste_costab/$1');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/supprimer_costab', 'Planification_demande_cl_cmr_costab::supprimer_costab');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/editercostab', 'Planification_demande_cl_cmr_costab::editercostab');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/save_costab', 'Planification_demande_cl_cmr_costab::save_costab');

	$routes->match(['get', 'post'], 'Planification_demande_cl_cmr_costab/save_form_cl_cmr_costab', 'Planification_demande_cl_cmr_costab::save_form_cl_cmr_costab');
	// Fin formulaire cl, cmr et costab
	#############################################################################################

	#################################################################################################
	//--------------- Debut Planification_Strategique_Sectorielle  --------------------------
	$routes->get('Planification_Strategique_Sectorielle/getOne/(:any)','Planification_Strategique_Sectorielle::getOne/$1');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/send_data', 'Planification_Strategique_Sectorielle::send_data');

	$routes->post('Planification_Strategique_Sectorielle/liste_historique', 'Planification_Strategique_Sectorielle::liste_historique');

	$routes->post('Planification_Strategique_Sectorielle/liste_cl_cmr_vision', 'Planification_Strategique_Sectorielle::liste_cl_cmr_vision');

	$routes->post('Planification_Strategique_Sectorielle/liste_cl_cmr_pap', 'Planification_Strategique_Sectorielle::liste_cl_cmr_pap');

	$routes->post('Planification_Strategique_Sectorielle/liste_cl_cmr_politique_sectorielle', 'Planification_Strategique_Sectorielle::liste_cl_cmr_politique_sectorielle');

	$routes->post('Planification_Strategique_Sectorielle/liste_costab_vision', 'Planification_Strategique_Sectorielle::liste_costab_vision');

	$routes->post('Planification_Strategique_Sectorielle/liste_costab_pap', 'Planification_Strategique_Sectorielle::liste_costab_pap');

	$routes->post('Planification_Strategique_Sectorielle/liste_costab_politique_sectorielle', 'Planification_Strategique_Sectorielle::liste_costab_politique_sectorielle');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/getDescriptionAction', 'Planification_Strategique_Sectorielle::getDescriptionAction');

	$routes->get('Planification_Strategique_Sectorielle/index/(:any)','Planification_Strategique_Sectorielle::index/$1');

	$routes->get("Planification_Strategique_Sectorielle/getObjectif/(:any)", "Planification_Strategique_Sectorielle::getObjectif/$1");

	$routes->get("Planification_Strategique_Sectorielle/getPilier/(:any)", "Planification_Strategique_Sectorielle::getPilier/$1");

	$routes->get("Planification_Strategique_Sectorielle/getAxeObjectif/(:any)", "Planification_Strategique_Sectorielle::getAxeObjectif/$1");

	$routes->get("Planification_Strategique_Sectorielle/getPlanification/(:any)", "Planification_Strategique_Sectorielle::getPlanification/$1");

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/liste_cl_cmr', 'Planification_Strategique_Sectorielle::liste_cl_cmr');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/supprimer_cl_cmr', 'Planification_Strategique_Sectorielle::supprimer_cl_cmr');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/editercl_cmr', 'Planification_Strategique_Sectorielle::editercl_cmr');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/update_save_cl_cmr', 'Planification_Strategique_Sectorielle::update_save_cl_cmr');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/liste_costab', 'Planification_Strategique_Sectorielle::liste_costab');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/supprimer_costab', 'Planification_Strategique_Sectorielle::supprimer_costab');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/editercostab', 'Planification_Strategique_Sectorielle::editercostab');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/update_save_costab', 'Planification_Strategique_Sectorielle::update_save_costab');

	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/update_form_cl_cmr_costab', 'Planification_Strategique_Sectorielle::update_form_cl_cmr_costab');


	$routes->match(['get', 'post'], 'Planification_Strategique_Sectorielle/associate_profil_etape', 'Planification_Strategique_Sectorielle::associate_profil_etape');
	//--------------- Fin Planification_Strategique_Sectorielle  --------------------------


	$routes->post('Dem_Liste_Activites/create_get_tutel', 'Dem_Liste_Activites::create_get_Sous_tutel');
	$routes->post('Dem_Liste_Activites/create_get_Code_article', 'Dem_Liste_Activites::create_get_code_article');
	$routes->post('Dem_Liste_Activites/create_get_Code_paragraphe','Dem_Liste_Activites::create_get_code_paragraphe');
	$routes->post('Dem_Liste_Activites/create_get_Code_littera', 'Dem_Liste_Activites::create_get_code_littera');
	$routes->post('Dem_Liste_Activites/create_get_sous_littera', 'Dem_Liste_Activites::create_get_code_sous_littera');
	$routes->post('Dem_Liste_Activites/code_article', 'Dem_Liste_Activites::Get_code_articles');
	$routes->post('Dem_Liste_Activites/code_division', 'Dem_Liste_Activites::Get_code_division');
	$routes->post('Dem_Liste_Activites/code_groupe', 'Dem_Liste_Activites::create_get_code_groupe');
	$routes->post('Dem_Liste_Activites/code_classe', 'Dem_Liste_Activites::create_get_code_classe');
	$routes->post('Dem_Liste_Activites/codeparagraphe', 'Dem_Liste_Activites::Get_code_Paragraphe');

	$routes->post('Dem_Liste_Activites/code_classes', 'Dem_Liste_Activites::Get_code_classes');
	$routes->post('Dem_Liste_Activites/code_sous_littera', 'Dem_Liste_Activites::Get_code_sous_litteras');
	$routes->post('Dem_Liste_Activites/code_sous_tutels', 'Dem_Liste_Activites::Get_code_sous_tutels');
	$routes->post('Dem_Liste_Activites/code_programmatique', 'Dem_Liste_Activites::Get_code_code_programmatique');

	$routes->post('Dem_Liste_Activites/code_divisions', 'Dem_Liste_Activites::Get_code_divisions');
	$routes->post('Dem_Liste_Activites/code_groupes', 'Dem_Liste_Activites::Get_code_groupe');
	$routes->post('Dem_Liste_Activites/code_class_id', 'Dem_Liste_Activites::Get_code_classe');
});
############### End module process ##################

############### Debut module ihm ##################
$routes->group('ihm', ['namespace' => 'App\Modules\ihm\Controllers'], function ($routes)
{
	$routes ->get('Execution','Execution::index');
	$routes->post('Execution/listing', 'Execution::listing');
	$routes->get('Execution/detail/(:any)','Execution::detail/$1');
	$routes->post('Execution/get_programme', 'Execution::get_programme');
	$routes->post('Execution/get_action', 'Execution::get_action');
	$routes->post('Execution/get_pap_activite', 'Execution::get_pap_activite');

	$routes->post('Fonctionnel/get_date_limit', 'Fonctionnel::get_date_limit');
	$routes->post('rapport_classification_admnistrative/get_date_limit', 'rapport_classification_admnistrative::get_date_limit');
	$routes ->get('Canvas_Suivie_evaluation_Deux/canvas_liste','Canvas_Suivie_evaluation_Deux::canvas_liste');
	$routes ->get('Canvas_Suivie_evaluation_Deux/export_excel/(:any)','Canvas_Suivie_evaluation_Deux::export_excel/$1');
	$routes ->post('Canvas_Suivie_evaluation_Deux/listing_Canvas_deux','Canvas_Suivie_evaluation_Deux::listing_Canvas_deux');
	$routes->get('Canvas_Suivie_evaluation_Deux/get_program/(:any)','Canvas_Suivie_evaluation_Deux::get_program/$1');
	$routes->post('Classification_Economique_deux/get_date_limit', 'Classification_Economique_deux::get_date_limit');
	$routes->get('Canvas_Suivie_evaluation_Deux/get_action/(:any)','Canvas_Suivie_evaluation_Deux::get_action/$1');
	//Début Liste - Canevas - Suivi Evaluation
	$routes->add('Canevas_Suivi_Evaluation_Un', 'Canevas_Suivi_Evaluation_Un::index');
	$routes->post('Canevas_Suivi_Evaluation_Un/listing', 'Canevas_Suivi_Evaluation_Un::listing');
	$routes->post('Canevas_Suivi_Evaluation_Un/get_prog', 'Canevas_Suivi_Evaluation_Un::get_prog');
	$routes->post('Canevas_Suivi_Evaluation_Un/get_action', 'Canevas_Suivi_Evaluation_Un::get_action');
	$routes->get('Canevas_Suivi_Evaluation_Un/export/(:any)', 'Canevas_Suivi_Evaluation_Un::export/$1');

	// ------------------------- Gestion activites en dehors du processus programmation-----------------
	$routes ->get('Ajout_New_Activites/ajout_activite','Ajout_New_Activites::ajout_activite');
	$routes->get('Ajout_New_Activites/get_sousTutel/(:any)','Ajout_New_Activites::get_sousTutel/$1');
	$routes->get('Ajout_New_Activites/get_Programmes/(:any)','Ajout_New_Activites::get_Programmes/$1');
	$routes->post('Ajout_New_Activites/code_class_id', 'Ajout_New_Activites::Get_code_classe');
	$routes->post('Ajout_New_Activites/create_get_action', 'Ajout_New_Activites::create_get_action');
	$routes->post('Ajout_New_Activites/code_groupes', 'Ajout_New_Activites::Get_code_groupe');
	$routes->post('Ajout_New_Activites/code_divisions', 'Ajout_New_Activites::Get_code_divisions');
	$routes->post('Ajout_New_Activites/code_programmatique', 'Ajout_New_Activites::Get_code_code_programmatique');
	$routes->post('Ajout_New_Activites/code_sous_tutels', 'Ajout_New_Activites::Get_code_sous_tutels');
	$routes->post('Ajout_New_Activites/create_get_sous_littera', 'Ajout_New_Activites::create_get_code_sous_littera');
	$routes->post('Ajout_New_Activites/code_sous_littera', 'Ajout_New_Activites::Get_code_sous_litteras');
	$routes->post('Ajout_New_Activites/code_classes', 'Ajout_New_Activites::Get_code_classes');
	$routes->post('Ajout_New_Activites/codeparagraphe', 'Ajout_New_Activites::Get_code_Paragraphe');
	$routes->post('Ajout_New_Activites/code_division', 'Ajout_New_Activites::Get_code_division');	
	$routes->post('Ajout_New_Activites/create_get_Code_article', 'Ajout_New_Activites::create_get_code_article');
	$routes->post('Ajout_New_Activites/code_article', 'Ajout_New_Activites::Get_code_articles');
	$routes->post('Ajout_New_Activites/code_classe', 'Ajout_New_Activites::create_get_code_classe');
	$routes->post('Ajout_New_Activites/code_groupe', 'Ajout_New_Activites::create_get_code_groupe');
	$routes->match(['get','post'],"Ajout_New_Activites/get_code_int/(:any)", "Ajout_New_Activites::get_code_int/$1");
	$routes->post('Ajout_New_Activites/create_get_Code_littera', 'Ajout_New_Activites::create_get_code_littera');
	$routes->post('Ajout_New_Activites/create_get_Code_paragraphe','Ajout_New_Activites::create_get_code_paragraphe');
	$routes->post('Ajout_New_Activites/get_programs', 'Ajout_New_Activites::get_programs');
	$routes->post('Ajout_New_Activites/save_activite', 'Ajout_New_Activites::save_activite');
	$routes->post('Ajout_New_Activites/tempo_liste', 'Ajout_New_Activites::tempo_liste');
	$routes->get('Ajout_New_Activites/ajouter', 'Ajout_New_Activites::ajouter');
	$routes->post('Ajout_New_Activites/Listing_new_activities', 'Ajout_New_Activites::Listing_new_activities');
	$routes->get('Ajout_New_Activites/liste_nouvelle_activites', 'Ajout_New_Activites::liste_nouvelle_activites');
	$routes->post('Ajout_New_Activites/supprimer', 'Ajout_New_Activites::supprimer');
	$routes->get('Ajout_New_Activites/get_one_activity/(:any)', 'Ajout_New_Activites::get_one_activity/$1');
	$routes->post('Ajout_New_Activites/modifier_tempo', 'Ajout_New_Activites::modifier_tempo');
	// ------------------------- Gestion activites en dehors du processus programmation-----------------
	$routes->get('rapport_classification_admnistrative/export_word/(:any)','rapport_classification_admnistrative::export_word/$1');
	$routes->get('rapport_classification_admnistrative/export_pdf/(:any)','rapport_classification_admnistrative::export_pdf/$1');
	$routes->get('Classification_Economique_deux/export_pdf/(:any)','Classification_Economique_deux::export_pdf/$1');
	$routes->post('Classification_Economique_deux/get_dep','Classification_Economique_deux::get_dep');
	////////////////////////////export rapport fonctionnel ////////////////////

	$routes->get('Fonctionnel/exporter_word/(:any)','Fonctionnel::exporter_word/$1');
	$routes->get('Fonctionnel/exporter_pdf/(:any)','Fonctionnel::exporter_pdf/$1');

	///////////////////////////////fin export rapport fonctionnel ///////////////////////////
	// -------------------------- debut secteur d'intervention --------------------------
	$routes ->get('Secteur_Intervention/list_view','Secteur_Intervention::liste_view');
	$routes ->post('Secteur_Intervention/liste_secteur','Secteur_Intervention::liste_secteur');
	$routes ->get('Secteur_Intervention/add_secteur','Secteur_Intervention::add_secteur');
	$routes ->post('Secteur_Intervention/Enregistrer_secteur','Secteur_Intervention::Enregistrer_secteur');
	$routes ->get('Secteur_Intervention/supprimer_secteur/(:any)','Secteur_Intervention::supprimer_secteur/$1');
	$routes ->get('Secteur_Intervention/modification_secteur/(:any)','Secteur_Intervention::modification_secteur/$1');
	$routes ->post('Secteur_Intervention/edit_secteur','Secteur_Intervention::edit_secteur');

	// ---------------------------fin secteur d' intervention -------------------------

	$routes->get('Classification_Economique_deux','Classification_Economique_deux::index');
	$routes->post('Classification_Economique_deux/filterChapitre','Classification_Economique_deux::filterChapitre');
	$routes->post('Classification_Economique_deux/filterInstitution','Classification_Economique_deux::filterInstitution');
	$routes->post('Classification_Economique_deux/getinstution','Classification_Economique_deux::getinstution');
	$routes->post('Classification_Economique_deux/listing', 'Classification_Economique_deux::listing');
	$routes->get('Classification_Economique_deux/exporter/(:any)','Classification_Economique_deux::exporter/$1');
	$routes->get('Classification_Economique_deux/export_word/(:any)','Classification_Economique_deux::export_word/$1');
	//--------------------------------------- crud information supplementaire
	$routes->get('Information_sup','Information_sup::index');
	$routes->post('Information_sup/listing','Information_sup::listing');
	$routes->get('Information_sup/new','Information_sup::new');
	$routes->post('Information_sup/save','Information_sup::save');
	$routes->get('Information_sup/getOne/(:any)','Information_sup::getOne/$1');
	$routes->post('Information_sup/update','Information_sup::update');
	$routes->get('Information_sup/is_active/(:any)','Information_sup::is_active/$1');
	$routes->get('Information_sup/delete/(:any)','Information_sup::delete/$1');
	
	//-------------------------------------fin information supplementaire

	//--------------------------------------- crud document
	$routes->get('Document','Document::index');
	$routes->post('Document/listing','Document::listing');
	$routes->get('Document/new','Document::new');
	$routes->post('Document/save','Document::save');
	$routes->get('Document/getOne/(:any)','Document::getOne/$1');
	$routes->post('Document/update','Document::update');
	$routes->get('Document/delete/(:any)','Document::delete/$1');
	//-------------------------------------fin crud document

	// ---------------------------- Debut CATEGORIE LIBELLE ------------------
	$routes->get('Categorie_Libelle/add','Categorie_Libelle::add_categorie');
	$routes->post('Categorie_Libelle/ajouter','Categorie_Libelle::ajouter_Categorie');
	$routes->get('Categorie_Libelle/liste_view','Categorie_Libelle::liste_view');
	$routes->post('Categorie_Libelle/liste_categorie','Categorie_Libelle::liste_categorie');
	$routes->get('Categorie_Libelle/edit_view/(:any)','Categorie_Libelle::edit_view/$1');
	$routes->post('Categorie_Libelle/edit_categorie','Categorie_Libelle::edit_categorie');
	$routes->get('Categorie_Libelle/supprimer_categorie/(:any)','Categorie_Libelle::supprimer_categorie/$1');

	$routes->get('Demandes','Demandes::index');
	$routes->post('Demandes/listing','Demandes::listing');
	$routes->post('Demandes/get_etapes','Demandes::get_etapes');

	//------------------------Debut crud des etapes --------------------------------------
	$routes->get('Proc_Etape','Proc_Etape::index');
	$routes->post('Proc_Etape/listing','Proc_Etape::listing');
	$routes->get('Proc_Etape/ajout','Proc_Etape::ajout');
	$routes->post('Proc_Etape/insert','Proc_Etape::insert');
	$routes->get('Proc_Etape/is_active/(:any)','Proc_Etape::is_active/$1');
	$routes->get('Proc_Etape/getOne/(:any)','Proc_Etape::getOne/$1');
	$routes->post('Proc_Etape/update','Proc_Etape::update');
	$routes->post('Proc_Etape/get_profil_etape','Proc_Etape::get_profil_etape');
	$routes->get('Proc_Etape/associate_profil_etape','Proc_Etape::associate_profil_etape');
  	//------------------------Fin crud des etapes ----------------------------------------

	//--------------------------------------- crud actions
	$routes->get('Actions','Actions::index');
	$routes->post('Actions/listing','Actions::listing');
	$routes->get('Actions/new','Actions::new');
	$routes->get('Actions/get_etape_suivante/(:any)','Actions::get_etape_suivante/$1');
	$routes->post('Actions/save','Actions::save');

	//-------------------------------------fin crud actions

	// ---------------------------- Debut CRUD ENJEUX -------------------
	$routes->get('Enjeux/add_enjeux','Enjeux::add_enjeux');
	$routes->post('Enjeux/ajouter_enjeux','Enjeux::ajouter_enjeux');
	$routes->get('Enjeux/liste_view','Enjeux::liste_view');
	$routes->post('Enjeux/liste_enjeux','Enjeux::liste_enjeux');
	$routes->get('Enjeux/edit_view/(:any)','Enjeux::edit_view/$1');
	$routes->post('Enjeux/edit_enjeux','Enjeux::edit_enjeux');
	$routes->get('Enjeux/supprimer_enjeux/(:any)','Enjeux::supprimer_enjeux/$1');
	// ---------------------------- Fin CRUD ENJEUX --------------------
	
	// ---------------------------- Debut CRUD PROCESS --------------------
	$routes->get('Processus','Processus::index');
	$routes->post('Processus/listing','Processus::listing');
	$routes->get('Processus/is_active/(:any)','Processus::is_active/$1');
	$routes->get('Processus/ajout','Processus::ajout');
	$routes->post('Processus/insert', 'Processus::insert');
	$routes->get('Processus/getOne/(:any)','Processus::getOne/$1');
	$routes->post('Processus/update','Processus::update');
	// ---------------------------- Fin CRUD PROCESS ----------------------

	// ---------------------------- Debut Observ. transfert financier -----------------------
	$routes->get('Observation_Financiere','Observation_Financiere::index');
	$routes->post('Observation_Financiere/listing','Observation_Financiere::listing');
	$routes->get('Observation_Financiere/is_active/(:any)','Observation_Financiere::is_active/$1');
	$routes->get('Observation_Financiere/ajout','Observation_Financiere::ajout');
	$routes->post('Observation_Financiere/insert', 'Observation_Financiere::insert');
	$routes->get('Observation_Financiere/getOne/(:any)','Observation_Financiere::getOne/$1');
	$routes->post('Observation_Financiere/update','Observation_Financiere::update');
	// ---------------------------- Fin Observ. transfert financier ----------------------

	//--------------------------------------- crud actions
	$routes->get('Actions','Actions::index');
	$routes->post('Actions/listing','Actions::listing');
	$routes->get('Actions/new','Actions::new');
	$routes->post('Actions/save','Actions::save');
	$routes->get('Actions/get_etape_suivante/(:any)','Actions::get_etape_suivante/$1');
	$routes->get('Actions/getOne/(:any)','Actions::getOne/$1');
	$routes->post('Actions/update','Actions::update');
	$routes->get('Actions/is_active/(:any)','Actions::is_active/$1');
	//----------------------------fin crud actions

	$routes->add('rapport_classification_admnistrative', 'rapport_classification_admnistrative::index');
	$routes->post('rapport_classification_admnistrative/listing', 'rapport_classification_admnistrative::listing');
	$routes->post('rapport_classification_admnistrative/get_soutut', 'rapport_classification_admnistrative::get_soutut');
	$routes->post('rapport_classification_admnistrative/get_prog', 'rapport_classification_admnistrative::get_prog');
	$routes->post('rapport_classification_admnistrative/get_action', 'rapport_classification_admnistrative::get_action');
	$routes->get('rapport_classification_admnistrative/export/(:any)', 'rapport_classification_admnistrative::export/$1');
	
	//Debut activités
	$routes->add('Liste_Activites', 'Liste_Activites::index');
	$routes->post('Liste_Activites/listing', 'Liste_Activites::listing');
	$routes->get('Liste_Activites/details/(:any)', 'Liste_Activites::details/$1');
	$routes->post('Liste_Activites/get_prog', 'Liste_Activites::get_prog');
	$routes->post('Liste_Activites/get_action', 'Liste_Activites::get_action');
	//Fin activités
	//Debut Détail activités
	$routes->get('Detail_Activite/(:any)','Detail_Activite::index/$1');
	//Fin Détail activités
	$routes->post('Detail_Institution/liste_ligne_budget/(:any)', 'Detail_Institution::liste_ligne_budget/$1');
	$routes->post('Detail_Programme/liste_ligne_budget/(:any)','Detail_Programme::liste_ligne_budget/$1');
	// --------------------------------------------------------------
	$routes->post('Detail_Institution/detail_rapport', 'Detail_Institution::detail_rapport');
	$routes->post('Fonctionnel/getinstution', 'Fonctionnel::getinstution');
	$routes->post('Classification_Economique/get_dep', 'Classification_Economique::get_dep');
	//Debut Détail action
	$routes->get('Detail_Action/(:any)','Detail_Action::index/$1');
	$routes->post('Detail_Action/liste_activite/(:any)','Detail_Action::liste_activite/$1');
	//Fin Détail action
	// ------------------------ Debut instirution ----------------------
	$routes->get('Institution','Institution::index');
	$routes->post('Institution/get_info', 'Institution::get_info');
	$routes->Add('Detail_Institution/(:any)', 'Detail_Institution::index/$1');
	$routes->post('Detail_Institution/liste_programme/(:any)', 'Detail_Institution::liste_programme/$1');
	$routes->post('Detail_Institution/liste_action/(:any)', 'Detail_Institution::liste_action/$1');
	$routes->post('Detail_Institution/liste_activite/(:any)', 'Detail_Institution::liste_activite/$1');
	// ------------------------ Fin instirution ----------------------

	$routes->get('Institutions_action','Institutions_action::index');
	$routes->post('Institutions_action/indexdeux', 'Institutions_action::indexdeux');
	$routes->post('Institutions_action/listing', 'Institutions_action::listing');
	$routes->get('Institutions_action/add', 'Institutions_action::ajout');
	$routes->post('Institutions_action/insert', 'Institutions_action::insert');
	$routes->get('Institutions_action/getOne/(:any)','Institutions_action::getOne/$1');
	$routes->post('Institutions_action/update','Institutions_action::update');
	$routes->get('Institutions_action/delete/(:any)','Institutions_action::delete/$1');

	// //Debut CRUD ptba institutions 
	// $routes->get('Ptba_Institution', 'Ptba_Institution::index');
	// $routes->post('Ptba_Institution/get_info', 'Ptba_Institution::get_info');
	// $routes->get('Ptba_Institution/nouvelle', 'Ptba_Institution::nouvelle');
	// $routes->post('Ptba_Institution/create', 'Ptba_Institution::create');
	// //Fin CRUD ptba institutions

	//Debut CRUD ptba Programme
	$routes->get('Programme', 'Programme::index');
	$routes->post('Programme/listing', 'Programme::listing');
	$routes->get('Programme/ajout', 'Programme::ajout');
	$routes->post('Programme/insert', 'Programme::insert');
	$routes->get('Programme/getOne/(:any)','Programme::getOne/$1');
	$routes->post('Programme/update','Programme::update');
	$routes->get('Programme/is_active/(:any)','Programme::is_active/$1');
	//Fin CRUD ptba Programme

	//Debut Détail programme
	$routes->get('Detail_Programme/(:any)','Detail_Programme::index/$1');
	$routes->post('Detail_Programme/liste_action/(:any)','Detail_Programme::liste_action/$1');
	$routes->post('Detail_Programme/liste_activite/(:any)','Detail_Programme::liste_activite/$1');
	//Fin Détail programme

	//routes demandes
	$routes->get('Liste_Engagement_Budgetaire', 'Liste_Engagement_Budgetaire::index');
	$routes->post('Liste_Engagement_Budgetaire/listing', 'Liste_Engagement_Budgetaire::listing');
	$routes->get('Liste_Engagement_Juridique', 'Liste_Engagement_Juridique::index');
	$routes->post('Liste_Engagement_Juridique/listing', 'Liste_Engagement_Juridique::listing');
	$routes->get('Liste_Liquidation', 'Liste_Liquidation::index');
	$routes->post('Liste_Liquidation/listing', 'Liste_Liquidation::listing');
	$routes->get('Liste_Ordonnancement', 'Liste_Ordonnancement::index');
	$routes->post('Liste_Ordonnancement/listing', 'Liste_Ordonnancement::listing');
	$routes->get('Liste_Decaissement', 'Liste_Decaissement::index');
	$routes->post('Liste_Decaissement/listing', 'Liste_Decaissement::listing');
	$routes->get('Liste_Paiement', 'Liste_Paiement::index');
	$routes->post('Liste_Paiement/listing', 'Liste_Paiement::listing');
	$routes->get('Exempleouvert', 'Exempleouvert::index');
	$routes->post('Exempleouvert/listing', 'Exempleouvert::listing');
	//fin demande


	$routes->get('Rapport_Suivi_Evaluation', 'Rapport_Suivi_Evaluation::index');
	$routes->post('Rapport_Suivi_Evaluation/liste', 'Rapport_Suivi_Evaluation::liste');
	$routes->get('Rapport_Suivi_Evaluation/get_programme/(:any)', 'Rapport_Suivi_Evaluation::get_programme/$1');
	$routes->get('Rapport_Suivi_Evaluation/get_imputation/(:any)', 'Rapport_Suivi_Evaluation::get_imputation/$1');
	$routes->get('Rapport_Suivi_Evaluation/get_action/(:any)', 'Rapport_Suivi_Evaluation::get_action/$1');
	$routes->get('Rapport_Suivi_Evaluation/exporter', 'Rapport_Suivi_Evaluation::exporter');
	$routes->get('Rapport_Suivi_Evaluation/exporter_filtre/(:any)', 'Rapport_Suivi_Evaluation::exporter_filtre/$1');
	$routes->get('Rapport_Suivi_Evaluation/exporter_word/(:any)', 'Rapport_Suivi_Evaluation::exporter_word/$1');
	$routes->get('Rapport_Suivi_Evaluation/exporter_pdf/(:any)', 'Rapport_Suivi_Evaluation::exporter_pdf/$1');

	$routes->Add('Rapport_Suivi_Evaluation_New', 'Rapport_Suivi_Evaluation_New::index');
	$routes->post('Rapport_Suivi_Evaluation_New/liste', 'Rapport_Suivi_Evaluation_New::liste');
	$routes->get('Rapport_Suivi_Evaluation_New/get_programme/(:any)', 'Rapport_Suivi_Evaluation_New::get_programme/$1');
	$routes->get('Rapport_Suivi_Evaluation_New/get_sous_tutelle/(:any)', 'Rapport_Suivi_Evaluation_New::get_sous_tutelle/$1');
	$routes->get('Rapport_Suivi_Evaluation_New/get_action/(:any)', 'Rapport_Suivi_Evaluation_New::get_action/$1');
	$routes->get('Rapport_Suivi_Evaluation_New/exporter', 'Rapport_Suivi_Evaluation_New::exporter');

	###############  Debut classification éconimique  ###############
	$routes->get('Classification_Economique','Classification_Economique::index');
	$routes->post('Classification_Economique/filterChapitre', 'Classification_Economique::filterChapitre');
	$routes->post('Classification_Economique/filterInstitution', 'Classification_Economique::filterInstitution');
	$routes->post('Classification_Economique/getinstution', 'Classification_Economique::getinstution');
	$routes->post('Classification_Economique/listing', 'Classification_Economique::listing');
	$routes->get('Classification_Economique/exporter/(:any)','Classification_Economique::exporter/$1');
	###############  Fin classification éconimique  ###############

	###############  Debut classification Fonctionnel  ###############
	$routes->get('Fonctionnel','Fonctionnel::index');
	$routes->post('Fonctionnel/get_dep', 'Fonctionnel::get_dep');
	$routes->post('Fonctionnel/listing', 'Fonctionnel::listing');
	$routes->get('Fonctionnel/exporter/(:any)', 'Fonctionnel::exporter/$1');
	###############  Fin classification Fonctionnel  ###############
	$routes->get('Rapport_contr/index', 'Rapport_contr::index');
	$routes->post('Rapport_contr/listing', 'Rapport_contr::listing');
	$routes->post('Rapport_contr/get_dep', 'Rapport_contr::get_dep');
	$routes->get('Rapport_contr/export/(:any)','Rapport_contr::export/$1');
});
############### Fin module ihm ####################

############### Debut module ptba ##################
$routes->group('ptba', ['namespace' => 'App\Modules\ptba\Controllers'], function ($routes)
{
	//Debut Détail action
	$routes->get('Detail_Action/(:any)','Detail_Action::index/$1');
	$routes->post('Detail_Action/liste_activite/(:any)','Detail_Action::liste_activite/$1');
	$routes->get('Traite_Donnees/Valide_Ptbas/(:any)','Traite_Donnees::Valide_Ptbas/$1');
	$routes->get('Traite_Donnees/invalide_Ptbas/(:any)','Traite_Donnees::invalide_Ptbas/$1');
	$routes->get('Traite_Donnees/edit_qte_view/(:any)','Traite_Donnees::edit_qte_view/$1');
	$routes->post('Traite_Donnees/save_modification','Traite_Donnees::save_modification');
	//Fin Détail action

	// ------------------------ Debut Detail instirution ----------------------
	$routes->get('Institution','Institution::index');
	$routes->post('Institution/get_info', 'Institution::get_info');
	$routes->Add('Detail_Institution/(:any)', 'Detail_Institution::index/$1');
	$routes->post('Detail_Institution/liste_programme/(:any)', 'Detail_Institution::liste_programme/$1');
	$routes->post('Detail_Institution/liste_action/(:any)', 'Detail_Institution::liste_action/$1');
	$routes->post('Detail_Institution/liste_activite/(:any)', 'Detail_Institution::liste_activite/$1');
	// ------------------------ Fin Detail instirution ----------------------
	$routes->post('Detail_Institution/liste_ligne_budget/(:any)', 'Detail_Institution::liste_ligne_budget/$1');
	//Debut Détail programme
	$routes->get('Detail_Programme/(:any)','Detail_Programme::index/$1');
	$routes->post('Detail_Programme/liste_action/(:any)','Detail_Programme::liste_action/$1');
	$routes->post('Detail_Programme/liste_activite/(:any)','Detail_Programme::liste_activite/$1');
	//Fin Détail programme

	//Debut Détail activités
	$routes->get('Dem_Detail_Activite/(:any)','Dem_Detail_Activite::index/$1');
	//Fin Détail activités

	// --------------- SELECT DES CLASS. ECO. ET FONCT. ----------------------------------
	$routes->post('Ptba_contr/get_code', 'Ptba_contr::get_code');
	$routes->post('Ptba_contr/get_parag', 'Ptba_contr::get_parag');
	$routes->post('Ptba_contr/get_litera', 'Ptba_contr::get_litera');
	$routes->post('Ptba_contr/get_sous_litera', 'Ptba_contr::get_sous_litera');
	$routes->post('Ptba_contr/get_groupes', 'Ptba_contr::get_groupes');
	$routes->post('Ptba_contr/get_classes', 'Ptba_contr::get_classes');
	// --------------- FIN SELECT DES CLASS. ECO. ET FONCT. ------------------------------- 
	/********************** DEBUT PTBA ACTION **************************/
	$routes->get('Ptba_Action','Ptba_Action::index');
	$routes->post('Ptba_Action/indexdeux', 'Ptba_Action::indexdeux');
	$routes->post('Ptba_Action/listing', 'Ptba_Action::listing');
	$routes->get('Ptba_Action/add', 'Ptba_Action::ajout');
	/********************** DEBUT PTBA ACTION **************************/
	$routes->add('Ptba_Programme', 'Ptba_Programme::index');
	$routes->post('Ptba_Programme/liste_ptba_programme', 'Ptba_Programme::liste_ptba_programme');
	$routes->add('Liste_Classification_Fonctionnelle', 'Liste_Classification_Fonctionnelle::index');
	$routes->post('Liste_Classification_Fonctionnelle/classification_liste', 'Liste_Classification_Fonctionnelle::classification_liste');
	$routes->post('Liste_Classification_Fonctionnelle/get_groupes', 'Liste_Classification_Fonctionnelle::get_groupes');
	$routes->post('Liste_Classification_Fonctionnelle/get_classes', 'Liste_Classification_Fonctionnelle::get_classes');
	//Debut CRUD ptba institutions 
	$routes->get('Ptba_Institution', 'Ptba_Institution::index');
	$routes->post('Ptba_Institution/get_info', 'Ptba_Institution::get_info');
	$routes->get('Ptba_Institution/nouvelle', 'Ptba_Institution::nouvelle');
	$routes->post('Ptba_Institution/create', 'Ptba_Institution::create');
	//Fin CRUD ptba institutions
	$routes->get('Ptba_contr/create', 'Ptba_contr::create');
	$routes->get('Ptba_contr/get_create', 'Ptba_contr::get_create');
	$routes->get('Ptba_contr/index', 'Ptba_contr::index');
	$routes->post('Ptba_contr/listing', 'Ptba_contr::listing');
	$routes->post('Ptba_contr/insert', 'Ptba_contr::insert');
	$routes->post('Ptba_contr/get_action', 'Ptba_contr::get_action');
	$routes->get('Ptba_contr/delete/(:any)', 'Ptba_contr::delete/$1');
	$routes->get('Ptba_contr/editOne/(:any)', 'Ptba_contr::editOne/$1');
	$routes->post('Ptba_contr/modifier', 'Ptba_contr::modifier');
	$routes->add('Liste_Ptba', 'Liste_Ptba::index');
	$routes->post('Liste_Ptba/listing', 'Liste_Ptba::listing');
	$routes->post('Liste_Ptba/get_soutut', 'Liste_Ptba::get_soutut');
	$routes->post('Liste_Ptba/get_prog', 'Liste_Ptba::get_prog');
	$routes->post('Liste_Ptba/get_action', 'Liste_Ptba::get_action');

	/********************** LISTE PTBA ADMINISTRATIVE ******************************/
	$routes->add('Liste_Ptba_Administrative', 'Liste_Ptba_Administrative::index');
	$routes->post('Liste_Ptba_Administrative/listing', 'Liste_Ptba_Administrative::listing');

	/********************** LISTE PTBA ECONOMIQUE ******************************/
	$routes->add('Liste_Ptba_Economique', 'Liste_Ptba_Economique::index');
	$routes->post('Liste_Ptba_Economique/listing', 'Liste_Ptba_Economique::listing');
	
	/********************** LES SELECTS POUR CLASS. ADMINISTRATIVE ******************************/
	$routes->post('Liste_Ptba_Administrative/get_soutut', 'Liste_Ptba_Administrative::get_soutut');
	$routes->post('Liste_Ptba_Administrative/get_prog', 'Liste_Ptba_Administrative::get_prog');
	$routes->post('Liste_Ptba_Administrative/get_action', 'Liste_Ptba_Administrative::get_action');

	/********************** LES SELECTS POUR CLASS. ECONOMIQUE***************************/
	$routes->post('Liste_Ptba_Economique/get_parag', 'Liste_Ptba_Economique::get_parag');
	$routes->post('Liste_Ptba_Economique/get_litera', 'Liste_Ptba_Economique::get_litera');
});
############### Fin module ptba ##################

############### Debut module geo ##################
$routes->group('geo', ['namespace' => 'App\Modules\geo\Controllers'], function ($routes)
{
	###############  Debut carte des intitution  ###############
	$routes->get('Carte_Institutions','Carte_Institutions::index');
	$routes->post('Carte_Institutions/index','Carte_Institutions::index');
	###############  Fin carte des intitution  ###############
});
############### Fin module geo ####################

if(is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
?>
