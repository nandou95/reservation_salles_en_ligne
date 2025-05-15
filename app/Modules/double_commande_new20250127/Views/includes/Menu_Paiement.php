<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
?>

<div class="table-responsive" style="width: 100%;">
	<div class="d-flex justify-content-between align-items-end">
		<a href="<?=base_url('double_commande_new/Liste_Paiement')?>" class="<?=$paiement_a_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_paie_fer')?></p></div> <div class="menu-link"><span><?=$paie_a_faire;?></span></div></a>

		<a href="<?=base_url('double_commande_new/Liste_Paiement_Deja_Fait')?>" class="<?=$paiement_deja_fait?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_paie_dejfet')?></p></div> <div class="menu-link"><span><?=$paie_deja_fait?></span></div></a>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Reception_Prise_Charge')?>" class="<?=$recep_prise_en_charge?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_recep_prise_en_charge')?></p></div> <div class="menu-link"><span><?=$recep_prise_charge?></span></div></a>

			<a href="<?=base_url('double_commande_new/Liste_Reception_Prise_Charge/deja_recep')?>" class="<?=$deja_recep_prise_en_charge?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_deja_recep_prise_en_charge')?></p></div> <div class="menu-link"><span><?=$deja_recep_prise_charge?></span></div></a>

			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Receptio_Border_Dir_compt')?>" class="<?=$recep_dir?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_recep_dir')?></p></div> <div class="menu-link"><span><?=$recep_dir_comptable?></span></div></a>

			<a href="<?=base_url('double_commande_new/Bordereau_Recu_Dir_Comptabilite')?>" class="<?=$deja_recep_dir?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_deja_recep_dir')?></p></div> <div class="menu-link"><span><?=$deja_recep_dir_comptable?></span></div></a>

			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Transmission_Directeur_Comptable_List')?>" class="<?=$bordereau_dc?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_dc')?></p></div> <div class="menu-link"><span><?=$get_bord_dc?></span></div></a>

			<a href="<?=base_url('double_commande_new/List_Bordereau_Deja_Transmsis')?>" class="<?=$bordereau_deja_dc?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_deja_dc')?></p></div> <div class="menu-link"><span><?=$get_bord_deja_dc?></span></div></a>

			<?php
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_transmission_bordereau_a_transmettre_brb')?>" class="<?=$bordereau_brb?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_brb')?></p></div> <div class="menu-link"><span><?=$get_bord_brb?></span></div></a>

			<a href="<?=base_url('double_commande_new/Liste_transmission_bordereau_deja_transmis_brb')?>" class="<?=$bordereau_deja_brb?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_deja_brb')?></p></div> <div class="menu-link"><span><?=$get_bord_deja_trans_brb?></span></div></a>
			<?php
		}

		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Validation_Titre/liste_valide_faire')?>" class="<?=$valid_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.TitresFait')?>  </p></div> <div class="menu-link"><span><?=$get_titre_valide?></span></div></a>

			<a href="<?=base_url('double_commande_new/Validation_Titre/liste_valide_termine')?>" class="<?=$valid_termnine?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.TitresDone')?> </p></div> <div class="menu-link"><span><?=$get_titre_termine?></span></div></a>

			<?php
		}

		?>


</div>
</div>