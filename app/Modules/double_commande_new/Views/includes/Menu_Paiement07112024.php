<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

?>

<div class="table-responsive" style="width: 100%;">
	<div class="d-flex justify-content-between align-items-end">
		
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
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_OBR')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Avant_OBR')?>" class="<?=$get_nbr_av_obr1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Paiement&nbsp;avant OBR<?//=lang('messages_lang.menu_deja_recep_prise_en_charge')?></p></div> <div class="menu-link"><span><?=$get_nbr_av_obr?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_obr')?>" class="<?=$reception_obr?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_recep_obr')?></p></div> <div class="menu-link"><span><?=$get_recep_obr?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_AVANT_PRISE_CHARGE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Avant_PC')?>" class="<?=$get_nbr_av_pc1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Paiement&nbsp;avant Prise&nbsp;en&nbsp;charge<?//=lang('messages_lang.menu_deja_recep_prise_en_charge')?></p></div> <div class="menu-link"><span><?=$get_nbr_av_pc?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_prise_charge')?>" class="<?=$prise_charge_compt?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_prise')?></p></div> <div class="menu-link"><span><?=$get_prise_charge?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_correct_pc')?>" class="<?= $sousmenu2 == 'vue_correct_pc' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_prise_correction')?></p></div> <div class="menu-link"><span><?=$get_prise_charge_corr?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_Annulation/annulation_pc')?>" class="<?= $sousmenu2 == 'annulation_pc' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.rejet_prise_charge')?></p></div> <div class="menu-link"><span><?=$get_etape_reject_pc?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_correct_etape')?>" class="<?= $sousmenu2 == 'vue_correct_etape' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_Decision_retour_Corr')?></p></div> <div class="menu-link"><span><?=$get_etape_corr?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_etab_titre')?>" class="<?=$etab_titre?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_etab_dec')?></p></div> <div class="menu-link"><span><?=$get_etab_titre?></span></div></a>

		<?php
		}
		?>



		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_correct_etab_titre')?>" class="<?= $sousmenu2 == 'vue_correct_etab_titre' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_etab_dec_corr')?></p></div> <div class="menu-link"><span><?=$get_etab_titre_corr?></span></div></a>

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
		if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_sign_dir_compt')?>" class="<?=$sign_dir_compt?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_titre_dir_compt')?></p></div> <div class="menu-link"><span><?=$get_sign_dir_compt?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_sign_dgfp')?>" class="<?=$sign_dir_dgfp?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_titre_dgfp')?></p></div> <div class="menu-link"><span><?=$get_sign_dir_dgfp?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE')==1)
		{
			?>

			<a href="<?=base_url('double_commande_new/Liste_Paiement/vue_sign_ministre')?>" class="<?=$sign_dir_min?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_titre_min')?></p></div> <div class="menu-link"><span><?=$get_sign_ministre?></span></div></a>

		<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Validation_Titre/liste_valide_faire')?>" class="<?=$valid_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.TitresFait')?>  </p></div> <div class="menu-link"><span><?=$get_titre_valide?></span></div></a>

			<a href="<?=base_url('double_commande_new/Validation_Titre/liste_valide_termine')?>" class="<?=$valid_termnine?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.TitresDone')?> </p></div> <div class="menu-link"><span><?=$get_titre_termine?></span></div></a>

			<?php
		}

		?>

		
		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_transmission_bordereau_a_transmettre_brb')?>" class="<?=$bordereau_brb?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_brb')?></p></div> <div class="menu-link"><span><?=$get_bord_brb?></span></div></a>

			<a href="<?=base_url('double_commande_new/Liste_transmission_bordereau_deja_transmis_brb')?>" class="<?=$bordereau_deja_brb?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_deja_brb')?></p></div> <div class="menu-link"><span><?=$get_bord_deja_trans_brb?></span></div></a>
			<?php
		}
		?>


</div>
</div>