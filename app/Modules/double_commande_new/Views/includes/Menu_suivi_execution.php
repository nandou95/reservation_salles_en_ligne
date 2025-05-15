<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">
    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_budj_corriger')?>" class="<?=$eng_budg_a_corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_eng_budg_wait_corr')?></p></div> <div class="menu-link" id='div_engag_budj_corriger'><span><?=$EBCORRIGE?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_budj_valide')?>" class="<?=$eng_budg_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_eng_budg_wait_valid')?></p></div> <div class="menu-link" id='div_engag_budj_valide'><span><?=$EBAVALIDE?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_faire')?>" class="<?=$eng_jurd_a_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_eng_jur_wait_faire')?></p></div> <div class="menu-link" id='div_engag_jurd_faire'><span><?=$EJFAIRE?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_corriger')?>" class="<?=$eng_jurd_a_corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_eng_jur_wait_corr')?></p></div> <div class="menu-link" id='div_engag_jurd_corriger'><span><?=$EJCORRIGER?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_valide')?>" class="<?=$eng_jurd_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_eng_jur_wait_valid')?></p></div> <div class="menu-link" id='div_engag_jurd_valide'><span><?=$EJVALIDER?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_faire')?>" class="<?=$liq_a_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_liquid_wait_faire')?></p></div> <div class="menu-link" id='div_liquidation_faire'><span><?=$LIQFAIRE?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_corrige')?>" class="<?=$liq_a_corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_liquid_wait_corr')?></p></div> <div class="menu-link" id='div_liquidation_corrige'><span><?=$LIQCORRIGER?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_valide')?>" class="<?=$liq_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_liquid_wait_valid')?></p></div> <div class="menu-link" id='div_liquidation_valide'><span><?=$LIQVALIDE?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/ordonnance_valide')?>" class="<?=$ord_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.titre_ordo_wait_faire')?></p></div> <div class="menu-link" id='div_ordonnance_valide'><span><?=$ORDVALIDE?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/prise_charge_attente_reception')?>" class="<?= $sousmenu2 == 'prise_charge_attente_reception' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_pc_recep')?></p></div> <div class="menu-link" id='prise_charge_a_recep'><span><?=$prise_charge_a_recep?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_etablissement')?>" class="<?= $sousmenu2 == 'titre_attente_etablissement' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_titre_att_recep')?></p></div> <div class="menu-link" id='titre_attente_etab'><span><?=$titre_attente_etab?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_correction')?>" class="<?= $sousmenu2 == 'titre_attente_correction' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_titre_att_corr')?></p></div> <div class="menu-link" id='titre_attente_corr'><span><?=$titre_attente_corr?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_reception_dir_compt')?>" class="<?= $sousmenu2 == 'titre_attente_reception_dir_compt' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_titre_att_recep_dir_comptable')?></p></div> <div class="menu-link" id='dir_compt_recep'><span><?=$dir_compt_recep?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_reception_obr')?>" class="<?= $sousmenu2 == 'titre_attente_reception_obr' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_titre_att_recep_obr')?></p></div> <div class="menu-link" id='obr_recep'><span><?=$obr_recep?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/decais_attente_traitement')?>" class="<?= $sousmenu2 == 'decais_attente_traitement' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_titre_att_trait')?></p></div> <div class="menu-link" id='dec_att_trait'><span><?=$dec_att_trait?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/decais_attente_recep_brb')?>" class="<?= $sousmenu2 == 'decais_attente_recep_brb' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.suivi_titre_att_recep_brb')?></p></div> <div class="menu-link" id='dec_att_recep_brb'><span><?=$dec_att_recep_brb?></span></div></a>

  </div>
</div>