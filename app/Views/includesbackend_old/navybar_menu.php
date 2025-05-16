<?php
$session  = \Config\Services::session();
if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
{
  return redirect('Login_Ptba/do_logout');
}
$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
?>
<nav id="sidebar" class="sidebar">
  <a class="sidebar-brand text-center" href="#">
    <img width="12rem" src="<?=base_url()?>/assets_new/images/minifinance-02.png">
  </a>
  <div class="sidebar-content">
    <div class="sidebar-user">
      <small> <i class="fa fa-user" aria-hidden="true"></i> <?php echo $session->get('SESSION_SUIVIE_PTBA_NOM')." ".$session->get('SESSION_SUIVIE_PTBA_PRENOM');?></small><br>
      <small> <i class="" aria-hidden="true"></i> <?php echo $session->get('SESSION_SUIVIE_PTBA_PROFIL_DESCRIPTION');?></small><br>
      <hr>
    </div>
    <ul class="sidebar-nav">
      <!-- DEBUT MENU ADMINISTRATION -->
      <?php
      if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')==1 || $session->get('SESSION_SUIVIE_PTBA_PROFIL')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu == 'Administration' ) echo ' active' ?>">
          <a href="#admin" data-toggle="collapse" class="sidebar-link collapsed "><i class="align-middle mr-2 fa fa-users"></i><span class="align-middle"><?=lang('messages_lang.menu_admin')?></span></a>
          <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php
            if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Gestion_Utilisateurs') echo'active'?>"><a class="sidebar-link"  href="<?=base_url()?>/Administration/Gestion_Utilisateurs"><?=lang('messages_lang.menu_gestion_user')?></a></li>
              <?php
            }

            if($session->get('SESSION_SUIVIE_PTBA_PROFIL')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='User_profil') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/Administration/User_profil"><?=lang('messages_lang.menu_gestion_profil')?></a></li>
              <?php
            }

            
            ?>
          </ul>
        </li>
        <?php
      }
      ?>
      <!-- FIN MENU ADMINISTRATION -->

      <!-- DEBUT MENU DASHBOARD -->
      <?php 
      if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PERFORMANCE_RACCROCHAGE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_TCD_ENGAGEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_EXECUTION_PHASE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TCD_VALEUR_PHASE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TCD_VALEUR_INSTITUTION')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PERFORMANCE_EXECUTION')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_BUDGET')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_EXECUTION_BUDGETAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_GRANDE_MASSE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TRANSFERT')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_REALISATION_PHYSIQUE')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu == 'dashboard') echo ' active' ?>">
          <a href="#tbord" data-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle mr-2 fa fa-dashboard"></i> <span class="align-middle"><?=lang('messages_lang.menu_tableau_bord')?></span>
          </a>
          <ul id="tbord" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PERFORMANCE_RACCROCHAGE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Performance_Decrochage') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Performance_Decrochage"><?=lang('messages_lang.menu_performance_raccrochage')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_TCD_ENGAGEMENT')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_TCD_Taux_Engagement') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_TCD_Taux_Engagement"><?=lang('messages_lang.menu_taux_TCD_engagement')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_EXECUTION_PHASE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Taux_Phase_Engagement') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Taux_Phase_Engagement"><?=lang('messages_lang.menu_taux_execution_phase')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TCD_VALEUR_PHASE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_TCD_Valeur_Engagement_Vote') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_TCD_Valeur_Engagement_Vote"><?=lang('messages_lang.menu_TCD_valeur_phase')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TCD_VALEUR_INSTITUTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Valeur_Phase_Engagement') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Valeur_Phase_Engagement"><?=lang('messages_lang.menu_TCD_valeur_institution')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TAUX_TCD_BUDGET_VOTE_INST')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Taux_Phase_Vote') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Taux_Phase_Vote"><?=lang('messages_lang.menu_TCD_budget_vote_institution')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PERFORMANCE_EXECUTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Performence_Excution') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Performence_Excution"><?=lang('messages_lang.menu_performance_execution')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_BUDGET')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashbord_General_Ptba') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashbord_General_Ptba"><?=lang('messages_lang.menu_budget')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_EXECUTION_BUDGETAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashbord_General_Execution') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashbord_General_Execution"><?=lang('messages_lang.menu_execution_budgetaire')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_VOTE_VS_EXECUTION_BUDG')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Comparaison_Budget') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Comparaison_Budget"><?=lang('messages_lang.menu_vote_execution_budgetaire')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_GRANDE_MASSE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Grande_Masse') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Grande_Masse"><?=lang('messages_lang.menu_grande_masse')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_ALLOCAT_BUDG_INSTITUTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Proportion_allocation_institution') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Proportion_allocation_institution"><?=lang('messages_lang.menu_allocation_budget_institution')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_DEPASSEMENT_BUDG_VOTE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Depassement_Budget_Vote') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Depassement_Budget_Vote"><?=lang('messages_lang.menu_depassement_budget_vote')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_TRANSFERT')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashboard_Transfert_budgetaire') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashboard_Transfert_budgetaire"><?=lang('messages_lang.label_tb_trans')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_REALISATION_PHYSIQUE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashbord_Suivi_Activite') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashbord_Suivi_Activite"><?=lang('messages_lang.label_realise_phys')?></a></li>
              <?php
            }
            ?>
          </ul>
        </li>
        <?php 
      }
      ?>
      <!-- FIN MENU DASHBOARD -->

        <!-- DEBUT MENU DASHBOARD PIP-->
      <?php
      if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_TDB_PIP')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_MINISTRE_INSTITUTION')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_FINANCEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_STATUT_PROJET')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_PILIER')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE')==1 || $session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_AXE_INTERVENTION')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu == 'dashboard') echo ' active' ?>">
          <a href="#tbordpip" data-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle mr-2 fa fa-area-chart"></i> <span class="align-middle"><?=lang('messages_lang.menu_tableau_bord_pip')?></span>
          </a>
          <ul id="tbordpip" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_TDB_PIP')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Dashbord_General_PIP') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Dashbord_General_PIP">TDB PIP</a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_MINISTRE_INSTITUTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Pip_Institution') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Pip_Institution"><?=lang('messages_lang.menu_ministere_institution')?> </a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_FINANCEMENT')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Pip_Source_Financement') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Pip_Source_Financement"><?=lang('messages_lang.menu_source_financement')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_PROGRAMME_BUDGETAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Pip_Programme_Budgetaire') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Pip_Programme_Budgetaire"><?=lang('messages_lang.menu_programme_budgetaire')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_STATUT_PROJET')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Repartition_Projet_Statut') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Repartition_Projet_Statut"><?=lang('messages_lang.menu_statut_projet')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_PILIER')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Pip_piliers') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Pip_piliers"><?=lang('messages_lang.menu_pilier')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_OBJECTIF_STRATEGIQUE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Objectif_strategique') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Objectif_strategique"><?=lang('messages_lang.menu_objectif_strategique')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_AXE_INTERVENTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Rapport_Projet_Intervention') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/dashboard/Rapport_Projet_Intervention"><?=lang('messages_lang.menu_axe_intervention')?></a></li>
              <?php
            }
            ?>
          </ul>
        </li>
        <?php
      }
      ?>
      <!-- FIN MENU DASHBOARD PIP -->

      <!-- DEBUT MENU DOUBLE COMMANDE -->
      <?php
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')==1 || $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')==1 || $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION')==1 || $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE')==1 || $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU')==1 || $session->get('SESSION_SUIVIE_PTBA_PAIEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE')==1 || $session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE')==1 || $session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB')==1 || $session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_PRISE_CHARGE')==1 || $session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB')==1 || $session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE')==1 || $session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_PRESTATAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD')==1 || $session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_OBR')==1 || $session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR')==1 || $session->get('SESSION_SUIVIE_PTBA_AVANT_PRISE_CHARGE')==1 || $session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1 || $session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE')==1 || $session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP')==1 || $session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE')==1 || $session->get('SESSION_SUIVIE_PTBA_CONTROLE_BESD')==1 || $session->get('SESSION_SUIVIE_PTBA_CONTROLE_BRB')==1 || $session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB_MINFIN')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu == 'double_commande_new') echo ' active' ?>">
          <a href="#doublecommande" data-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle mr-2 fa fa-tasks"></i> <span class="align-middle"><?=lang('messages_lang.label_droit_double_com')?></span>
          </a>
          <ul id="doublecommande" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php 
            if ($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu."/etape1" == 'double_commande_new/Phase_Administrative_Budget/etape1') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Phase_Administrative_Budget/etape1"><?=lang('messages_lang.soumen_intro_eng_budg')?></a></li>
              <?php
            }

            if ($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_SANS_BON')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CORRECTION')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu=="double_commande_new/Menu_Engagement_Budgetaire" || $menu."/".$sousmenu=="double_commande_new/Phase_Administrative_Budget") echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide"><?=lang('messages_lang.soumen_eng_budg')?></a></li>
              <?php
            }
            ?>

            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu=='double_commande_new/Menu_Engagement_Juridique' || $menu."/".$sousmenu."/eng_juridique"=='double_commande_new/Phase_Administrative/eng_juridique' || $menu."/".$sousmenu."/confirmer_juridique"=='double_commande_new/Phase_Administrative/confirmer_juridique') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Menu_Engagement_Juridique/eng_jur_deja_valide"><?=lang('messages_lang.soumen_eng_jur')?></a></li>
              <?php
            }
            ?>

            <?php 
            if ($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')==1 || $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu=='double_commande_new/Liquidation_Double_Commande' || $menu."/".$sousmenu=='double_commande_new/Liquidation') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liquidation_Double_Commande/get_liquid_valider"><?=lang('messages_lang.labelle_liquidation')?></a></li>
              <?php 
            }

            if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE')==1 || $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU')==1)
            { 
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu=='double_commande_new/Ordonnancement_Double_Commande' || $menu."/".$sousmenu=='double_commande_new/Liquidation') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Ordonnancement_Double_Commande/get_ordon_deja_fait"><?=lang('messages_lang.labelle_ordonan')?></a></li>
              <?php
            }

            if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE')==1 || $session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD')==1 || $session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_OBR')==1 || $session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR')==1 || $session->get('SESSION_SUIVIE_PTBA_AVANT_PRISE_CHARGE')==1 || $session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1 || $session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT')==1 || $session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE')==1 || $session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP')==1 || $session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE')==1)
            {
              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liste_Paiement"><?=lang('messages_lang.labelle_paiement')?></a></li>
              <?php 
            }

            if ($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT')==1)
            {
              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liste_Decaissement"><?=lang('messages_lang.labelle_decaisse')?></a></li>
              <?php
            }

            if ($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT')!=1 && $session->get('SESSION_SUIVIE_PTBA_CONTROLE_BESD')==1 )
            {
              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Controles_Decaissement/controle_besd"><?=lang('messages_lang.labelle_decaisse')?></a></li>
              <?php
            }

            if ($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu."/etape1" == 'double_commande_new/Introduction_Budget_Multi_Taches/etape1') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Introduction_Budget_Multi_Taches/etape1"><?=lang('messages_lang.intro_multi_tache')?></a></li>
              <?php
            }

            if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')==1)
            {
              ?>
              
              <li class="sidebar-item<?php if($sousmenu=='Suivi_Execution') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Suivi_Execution/engag_budj_corriger"><?=lang('messages_lang.suivi_execution')?></a></li>
              <?php
            }

            if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')==1)
            {
              ?>
              
              <li class="sidebar-item<?php if($sousmenu=='Montant_Execution_Par_Tache') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Montant_Execution_Par_Tache/get_liste">Exécution</a></li>
              <?php
            }


            if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT')==1)
            {
              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Etat_avancement"><?=lang('messages_lang.soumen_etat_avanc')?></a></li>
              <?php
            }
            ?>

            <?php 
            if ($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_PRESTATAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Prestataire') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Prestataire"><?=lang('messages_lang.soumen_prestat')?></a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_PROFIL_ID')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Taux_De_Change') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Taux_De_Change"><?=lang('messages_lang.label_droit_taux')?></a></li>
              <?php
            }
            ?>
               <?php
             if($session->get('SESSION_SUIVIE_PTBA_PROFIL')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Signataire_Note') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Signataire_Note/liste">Signataire Note</a></li>
              <?php
            }
              ?>
          </ul>
        </li>
        <?php
      }
      ?>
      <!-- FIN MENU DOUBLE COMMANDE -->
      
      <!-- DEBUT DOUBLE COMMANDE SALAIRE-->
       <?php
       if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION_SALAIRE')==1|| $session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE')==1|| $session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE')==1|| $session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET')==1|| $session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS')==1|| $session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE')==1|| $session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE')==1|| $session->get('SESSION_SUIVIE_PTBA_VALIDATION_SALAIRE_NET')==1|| $session->get('SESSION_SUIVIE_PTBA_VALIDATION_RETENUS_SALAIRE')==1|| $session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_ORDONANCEMENT_SALAIRE')==1 ){ ?>

         <li class="sidebar-item<?php if($menu == 'double_commande_new') echo ' active' ?>">
          <a href="#doublecommande_salaire" data-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle mr-2 fa fa-tasks"></i> <span class="align-middle">Double Commande salaire</span>
          </a>
          <ul id="doublecommande_salaire" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php 
            if ($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu."/etape1" == 'double_commande_new/Liquidation_Salaire/add') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liquidation_Salaire/add">Introduction salaire</a></li>
              <?php
            }

            if ($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu."/liste_autre_retenu" == 'double_commande_new/Liquidation_Salaire_Liste/liste_autre_retenu') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liquidation_Salaire_Liste/liste_autre_retenu">Saisie des autres retenues</a></li>
              <?php
            }

            if ($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_CORRECTION_LIQUIDATION_SALAIRE')==1)
            {

              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu=="double_commande_new/Liquidation_Salaire_Liste/index_Deja_Fait" || $menu."/".$sousmenu=="double_commande_new/Liquidation_Salaire_Liste/index_Deja_Fait") echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liquidation_Salaire_Liste/index_Deja_Fait"><?=lang('messages_lang.labelle_liquidation')?></a></li>
              <?php
            }
            ?>


            <?php 
         

            if($session->get('SESSION_SUIVIE_PTBA_ORDONANCEMENT_SALAIRE')==1)
            { 
              ?>
              <li class="sidebar-item<?php if($menu."/".$sousmenu=='double_commande_new/Ordonnancement_Double_Commande' || $menu."/".$sousmenu=='double_commande_new/Liquidation') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Ordonnancement_Salaire_Liste/index_Deja_Fait"><?=lang('messages_lang.labelle_ordonan')?></a></li>
              <?php
            }

            if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS')==1 || $session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET')==1 || $session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_VALIDATION_SALAIRE_NET')==1 || $session->get('SESSION_SUIVIE_PTBA_VALIDATION_RETENUS_SALAIRE')==1)
            {

              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Paiement_Salaire_Liste/vue_prise_charge"><?=lang('messages_lang.labelle_paiement')?></a></li>
              <?php 
            }

            if ($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE')==1)
            {
              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Decaissement_Salaire_Liste/vue_decaiss_faire"><?=lang('messages_lang.labelle_decaisse')?></a></li>
              <?php
            }

              if ($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION_SALAIRE')==1)
            {
              ?>
              <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Liquidation_Salaire/add_benef">Bénéficiaires</a></li>
              <?php
            }


           
            ?>


           
          </ul>
        </li>


     <?php
       }
       ?>
      <!-- FIND DOUBLE COMMANDE SALAIRE-->
      <!-- DEBUT MENU PLAFONNAGE -->
      <?php
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu."/".$sousmenu == 'double_commande_new/Transfert_Double_Commande' || $menu."/".$sousmenu == 'double_commande_new/Transfert_Meme_Activite') echo ' active' ?>">
          <a href="#doublecommande" data-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle mr-2 fa fa-tasks"></i> <span class="align-middle">Plafonnage</span>
          </a>
          <ul id="doublecommande" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Transfert_Double_Commande/add"><?=lang('messages_lang.soumen_transf_activ')?></a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url()?>/double_commande_new/Transfert_Meme_Activite/add"><?=lang('messages_lang.soumen_trim')?></a></li>
          </ul>
        </li>
        <?php
      }
      ?>
      <!-- DEBUT MENU PLAFONNAGE -->

       <!-- DEBUT PIP -->
      <?php
      if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')==1 || $session->get('SESSION_SUIVIE_PTBA_PIP_COMPILE')==1 || $session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')==1 || $session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')==1 || $session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu == 'pip' ) echo ' active' ?>">
          <a href="#pip" data-toggle="collapse" class="sidebar-link collapsed "><i class="align-middle mr-2 fa fa-sitemap"></i> <span class="align-middle">PIP</span></a>
          <ul id="pip" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='process') echo'active'?>"><a class="sidebar-link"  href="<?=base_url()?>/pip/Processus_Investissement_Public/demande"><?=lang('messages_lang.menu_remplissage_fiche_projet')?></a></li>
              <li class="sidebar-item<?php if($sousmenu=='Projet_Pip_Infini' || $sousmenu=='Projet_Pip_Fini' || $sousmenu=='Projet_Pip_Corrige' || $sousmenu=='Projet_Pip_Valide') echo' active'?>"><a class="sidebar-link"  href="<?=base_url()?>/pip/Projet_Pip_Infini/liste_pip_infini"><?=lang('messages_lang.menu_projet')?></a></li>
              <?php
            }
            ?>

            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_PIP_COMPILE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Fiche_Pip_Proposer' || $sousmenu=='Projet_Pip_A_Compiler' || $sousmenu=='Fiche_Pip_Corriger' || $sousmenu=='Fiche_Pip_Valider') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/pip/Fiche_Pip_Proposer/liste_pip_proposer"><?=lang('messages_lang.menu_compilation')?></a></li>
              <?php
            }
            ?>

            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Pip_projet_par_ministere_libvrable_projet') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/pip/Pip_projet_par_ministere_libvrable_projet/"><?=lang('messages_lang.menu_livrable_indicateur')?></a></li>
              <?php
            }
            ?>

            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_PIP_TAUX_ECHANGE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Taux_Echange') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/pip/Taux_Echange/"><?=lang('messages_lang.menu_taux_change')?></a></li>
              <?php
            }
            ?>

            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_PIP_POURCENTAGE_NOMENCLATURE')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Nomenclature_Pourcentage') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/pip/Nomenclature_Pourcentage/liste_pourcentage_nomenclature"><?=lang('messages_lang.menu_pourcentage_nomenclature')?></a></li>
              <?php
            }
            ?>

            <?php 
            if($session->get('SESSION_SUIVIE_PTBA_PIP_SOURCE_FINANCEMENT')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu=='Source_finance_bailleur') echo ' active'?>"><a class="sidebar-link" href="<?=base_url()?>/pip/Source_finance_bailleur"><?=lang('messages_lang.menu_source_financement')?></a></li>
              <?php
            }
            ?>
          </ul>
        </li>
        <?php
      }
      ?>
      <!-- FIN PIP -->

  
      <!-- DEBUT MENU PROCESS -->
      <?php
      if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE')==1 || $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT')==1 || $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_DEMANDE_ETAT_AVANCEMENT')==1)
      {
        ?>
        <li class="sidebar-item<?php if($menu == 'process') echo ' active' ?>">
          <a href="#demande" data-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle mr-2 fa fa-list"></i> <span class="align-middle"><?=lang('messages_lang.menu_demande')?></span>
          </a>
          <ul id="demande" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
            <?php
            if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE')==1)
            {
              ?>
              <li class="sidebar-item <?php if($sousmenu=='Demandes') echo 'active' ?>"><a class="sidebar-link" href="<?=base_url()?>/process/Demandes"><?=lang('messages_lang.menu_planification')?> Stratégique Sectorielle</a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT')==1)
            {
              ?>
              <li class="sidebar-item <?php if($sousmenu=='Demandes_CDMT_CBMT') echo 'active' ?>"><a class="sidebar-link" href="<?=base_url()?>/process/Demandes_CDMT_CBMT"><?=lang('messages_lang.menu_')?> CDMT CBMT</a></li>
              <?php
            }
            ?>

            <?php
            if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE')==1)
            {
              ?>
              <li class="sidebar-item <?php if($sousmenu=='Demandes_Program_Budget') echo 'active' ?>"><a class="sidebar-link" href="<?=base_url()?>/process/Demandes_Program_Budget"><?=lang('messages_lang.menu_')?></a></li>
              <?php
            }
            ?>

            <!-- DEBUT ETAT D'AVANCEMENT -->
            <?php
            if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_ETAT_AVANCEMENT')==1)
            {
              ?>
              <li class="sidebar-item<?php if($sousmenu == 'Etat_avancement') echo ' active' ?>"><a class="sidebar-link" href="<?=base_url()?>/process/Etat_avancement"><?=lang('messages_lang.menu_etat_avancement')?> </a></li>
              <?php
            }
            ?>
            <!-- DEBUT ETAT D'AVANCEMENT -->
          </ul>
        </li>
        <?php
      }
      ?>
      <!-- DEBUT MENU PROCESS -->
    </ul>
  </div>
</nav>