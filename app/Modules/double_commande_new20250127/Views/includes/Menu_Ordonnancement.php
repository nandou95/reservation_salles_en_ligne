<?php 
$session  = \Config\Services::session();
?>
<div class="table-responsive" style="width: 100%;">
	<div class="d-flex justify-content-between align-items-end">
		<?php
			if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')==1)
			{
				?>
				<a href="<?=base_url('double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire')?>" class="<?=$get_ordon_Afaire1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_ordo_faire')?></p></div> <div class="menu-link"><span><?=$get_ordon_Afaire?></span></div></a>
				<?php
			}

			if ($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE')==1)
			{
				?>
				<a href="<?=base_url('double_commande_new/Ordonnancement_Double_Commande/get_ordon_deja_fait')?>" class="<?=$get_ordon_deja_fait1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_ordo_faits')?></p></div> <div class="menu-link"><span><?=$get_ordon_deja_fait?></span></div></a>
				<?php
			}

			if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_TRANSMISSION_BORDEREAU')==1)
			{
				?>
				<a href="<?=base_url('double_commande_new/Liste_Trans_PC')?>" class="<?=$bordereau_spe?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_spe')?></p></div> <div class="menu-link"><span><?=$get_bord_spe?></span></div></a>

				<a href="<?=base_url('double_commande_new/Liste_Trans_Deja_Fait_PC')?>" class="<?=$bordereau_deja_spe?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_deja_spe')?></p></div> <div class="menu-link"><span><?=$get_bord_deja_spe?></span></div></a>
				<?php
			}

			?>
			
	</div>
</div>