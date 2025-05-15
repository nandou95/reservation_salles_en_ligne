<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">
    <?php
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_SANS_BON')==1)
    {
    ?>
      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/get_sans_bon_engagement')?>" class="<?=$eng_budg_sans_be ?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_budg_sans_bon')?></p></div> <div class="menu-link"><span><?=$SBE?></span></div></a>
    <?php
    }
    ?>

    <?php
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait')?>" class="<?=$eng_budg_deja_fait?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_budg_faire')?></p></div> <div class="menu-link"><span><?=$EBF?></span></div></a>

      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Corr')?>" class="<?=$eng_budg_a_corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_budg_corrij')?></p></div> <div class="menu-link"><span><?=$EBCorr?></span></div></a>
      <?php
    }
    ?>

    <?php
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_A_Valide')?>" class="<?=$eng_budg_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_budg_val')?></p></div> <div class="menu-link"><span><?=$EBAV?></span></div></a>
      <?php
    }
    ?>

    <?php
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide')?>" class="<?=$eng_budg_deja_valide?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_budg_dejvalCED')?></p></div> <div class="menu-link"><span><?=$EBDV?></span></div></a>
      <?php
    }
    ?>

    <?php
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER')==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/rejete_interface')?>" class="<?=$eng_budg_rej?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_budg_rej')?></p></div> <div class="menu-link"><span><?=$nbr_eng_rej?></span></div></a>
    <?php
    }
    ?>
    <?php
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_ANNULER')==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/rejete_fin')?>" class="<?= $sousmenu2 == 'rejete_fin' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.annulation_fin')?></p></div> <div class="menu-link"><span><?=$nbr_fin_rej?></span></div></a>

    <?php
    }
    ?>
  </div>
</div>