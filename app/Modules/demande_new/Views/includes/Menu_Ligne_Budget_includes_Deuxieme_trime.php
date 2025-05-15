<div class="d-flex justify-content-between align-items-end">
	<a href="<?=base_url('demande_new/Raccrochage_Deuxieme_Trim')?>" class="<?=$racrochet?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.budg_à_raccrocher')?></p></div> <div class="menu-link"><span ><?=$get_racrochet?></span></div></a>

	<a href="<?=base_url('demande_new/execution_raccroche/budget_deja_raccroche')?>" class="<?=$deja_racrochet?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.lign_budg_dej')?></p></div> <div class="menu-link"><span><?=$get_deja_racrochet?></span></div></a>
	
	<a href="<?=base_url('demande_new/Exec_Budget_Raccrocher_Trim2')?>" class="<?=$pas_qte_phys?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.raccr_sns_ind')?></p></div> <div class="menu-link"><span><?=$get_pas_qte_phys?></span></div></a>

	<a href="<?=base_url('demande_new/Ligne_Budget_Qte_phys_Trim2')?>" class="<?=$qte_phys?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.act_racc')?></p></div> <div class="menu-link"><span><?=$get_qte_phys?></span></div></a>
</div>