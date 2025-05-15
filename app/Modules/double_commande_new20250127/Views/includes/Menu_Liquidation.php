<?php
$session  = \Config\Services::session();
?>
<div class="table-responsive" style="width: 100%;">
	<div class="d-flex justify-content-between align-items-end">
		<?php
		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire')?>" class="<?=$get_liquid_Afaire1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_fer')?></p></div> <div class="menu-link"><span><?=$get_liquid_Afaire?></span></div></a>
			<a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_deja_fait')?>" class="<?=$get_liquid_deja_fait1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_djfer')?></p></div> <div class="menu-link"><span><?=$get_liquid_deja_fait?></span></div></a>
			<a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Acorriger')?>" class="<?=$get_liquid_Acorriger1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_corr')?></p></div> <div class="menu-link"><span><?=$get_liquid_Acorriger?></span></div></a>

			<a href="<?=base_url('double_commande_new/Liquidation/get_liquid_partiel')?>" class="<?=$get_liquid_partielle1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_partiel')?></p></div> <div class="menu-link"><span><?=$get_liquid_partielle?></span></div></a>
			<?php
		}

		if($session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_Avalider')?>" class="<?=$get_liquid_Avalider1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_val')?></p></div> <div class="menu-link"><span><?=$get_liquid_Avalider?></span></div></a>
			<?php
		}

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION')==1 || $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liquidation_Double_Commande/get_liquid_valider')?>" class="<?=$get_liquid_valider1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_dejval')?></p></div> <div class="menu-link"><span><?=$get_liquid_valider ?></span></div></a>

			<a href="<?=base_url('double_commande_new/Liquidation/get_liquidation_rejeter')?>" class="<?=$get_liquid_rejeter1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_liquid_rej')?></p></div> <div class="menu-link"><span><?=$get_liquid_rejeter?></span></div></a>
			<?php
		}
		?>
	</div>
</div>