<div class="d-flex justify-content-between align-items-end" style="float:center">
   
   <a href="<?=base_url('transfert_new/Transfert_list')?>" class="<?=$historique?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_hist_transferts')?></p></div> <div class="menu-link"><span ><?=$nbre_tr_hist?></span></div></a>

   <a href="<?=base_url('transfert_new/Transfert_incrim')?>" class="<?=$incrementation?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.transf_aliment')?></p></div> <div class="menu-link"><span ><?=$nbre_incrim?></span></div></a>

   <a href="<?=base_url('transfert_new/Transfert')?>" class="<?=$imputation?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.transferte_en_imputation')?></p></div> <div class="menu-link"><span ><?=$nbre_imput?></span></div></a>
   <a href="<?=base_url('transfert_new/Liste_Transfert_Entre_Activite')?>" class="<?=$deux_activite?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.transf_entr_act')?></p></div> <div class="menu-link"><span ><?=$nbre_activite?></span></div></a>

</div>