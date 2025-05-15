<?php 
$session  = \Config\Services::session();
?>
<div class="table-responsive" style="width: 100%;">
	<div class="d-flex justify-content-between align-items-end">
		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire')?>" class="<?=$get_ordon_Afaire1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_ordo_faire')?></p></div> <div class="menu-link" id="get_ordon_Afaire"><span><?=$get_ordon_Afaire?></span></div></a>
			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_MINISTRE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire_sup')?>" class="<?=$get_ordon_Afaire_sup1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.ordonnancement_cabinet')?></p></div> <div class="menu-link" id="get_ordon_Afaire_sup"><span><?=$get_ordon_Afaire_sup?></span></div></a>
			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_DEJA_VALIDE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Ordonnancement_Double_Commande/get_ordon_deja_fait')?>" class="<?=$get_ordon_deja_fait1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_ordo_faits')?></p></div> <div class="menu-link" id="get_ordon_deja_fait"><span><?=$get_ordon_deja_fait?></span></div></a>
			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_PRISE_CHARGE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_Trans_PC')?>" class="<?=$bordereau_spe?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.bord_spe')?></p></div> <div class="menu-link"><span><?=$get_bord_spe?></span></div></a>

			<a href="<?=base_url('double_commande_new/Liste_Trans_Deja_Fait_PC')?>" class="<?=$bordereau_deja_spe?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Les&nbsp;bons&nbsp;d'engagement déjà&nbsp;transmis Prise&nbsp;en&nbsp;charge</p></div> <div class="menu-link" id="get_bord_deja_spe"><span><?=$get_bord_deja_spe?></span></div></a>
			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BON_CABINET')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Ordonnancement_Ministre')?>" class="<?=$get_ordon_AuCabinet1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.trans_bon_eng_cabinet')?></p></div> <div class="menu-link" id="get_ordon_AuCabinet"><span><?=$get_ordon_AuCabinet?></span></div></a>
			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_CABINET_SPE')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_Trans_PC_Ministre')?>" class="<?=$get_ordon_BorderCabinet1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.trans_bord_cabinet_spe')?></p></div> <div class="menu-link" id="get_ordon_BorderCabinet"><span><?=$get_ordon_BorderCabinet?></span></div></a>
			<?php
		}
		?>

		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT_CORRECTION_CED')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Ordonna_Dir_Budg_Vers_Ced/liste')?>" class="<?=$get_ordon_BonCED1?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.trans_bon_eng_ced')?></p></div> <div class="menu-link" id="get_ordon_BonCED"><span><?=$get_ordon_BonCED?></span></div></a>
			<?php
		}
		?>
		<?php
		if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')==1)
		{
			?>
			<a href="<?=base_url('double_commande_new/Liste_Annulation/annulation_ordo')?>" class="<?= $sousmenu2 == 'annulation_ordo' ? 'btn-menu btn active' : 'btn-menu btn'?>"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.rejet_ordo')?></p></div> <div class="menu-link" id="get_etape_reject_ordo"><span><?=$get_etape_reject_ordo?></span></div></a>

		<?php
		}
		?>
	</div>
</div>