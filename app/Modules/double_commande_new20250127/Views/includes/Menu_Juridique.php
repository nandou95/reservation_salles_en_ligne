<?php
$session  = \Config\Services::session();
?>
<div class="table-responsive" style="width: 100%;">
	<div class="d-flex justify-content-between align-items-end">
		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Menu_Engagement_Juridique')?>" class="<?=$get_jurid_Afaire1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_jur_faire')?></p></div> <div class="menu-link"><span><?=$get_jurid_Afaire?></span></div></a>
			<a href="<?=base_url('double_commande_new/Menu_Engagement_Juridique/eng_jur_deja_fait')?>" class="<?=$get_jurid_deja_fait1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_jur_faits')?></p></div> <div class="menu-link"><span><?=$get_jurid_deja_fait?></span></div></a>
			<a href="<?=base_url('double_commande_new/Menu_Engagement_Juridique/eng_jur_corriger')?>" class="<?=$get_jurid_Acorriger1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_jur_corriger')?></p></div> <div class="menu-link"><span><?=$get_jurid_Acorriger?></span></div></a>
			<?php 
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Menu_Engagement_Juridique/eng_jur_valider')?>" class="<?=$get_jurid_Avalider1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_jur_valider')?></p></div> <div class="menu-link"><span><?=$get_jurid_Avalider?></span></div></a>
			<?php 
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')==1 || $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Menu_Engagement_Juridique/eng_jur_deja_valide')?>" class="<?=$get_jurid_valider1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_jur_valid')?></p></div> <div class="menu-link"><span><?=$get_jurid_valider?></span></div></a>
			<a href="<?=base_url('double_commande_new/Menu_Engagement_Juridique/eng_jur_rejeter')?>" class="<?=$get_jurid_rejeter?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_jur_rejeter')?></p></div> <div class="menu-link"><span><?=$get_jurid_Arejeter?></span></div></a>
			<?php 
		}
		?>
	</div>
</div>