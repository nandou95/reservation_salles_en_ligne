<div class="d-flex justify-content-between align-items-end">
	<a href="<?=base_url('pip/Projet_Pip_A_Compiler/liste_pip_compiler')?>" class="<?=$compiler?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_projet_compiler') ?></p></div><div class="menu-link"><span><?=$compilation?></span></div></a>

	<a href="<?=base_url('pip/Fiche_Pip_Proposer/liste_pip_proposer')?>" class="<?=$proposer?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_pip_propose') ?></p></div><div class="menu-link"><span><?=$pip_proposer?></span></div></a>

	<a href="<?=base_url('pip/Fiche_Pip_Corriger/liste_pip_corriger')?>" class="<?=$corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_projet_propose_corriger') ?></p></div><div class="menu-link"><span><?=$pip_corriger?></span></div></a>

	<a href="<?=base_url('pip/Fiche_Pip_Valider/liste_pip_Valider')?>" class="<?=$valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_pip_valide') ?></p></div><div class="menu-link"><span><?=$pip_valider?></span></div></a>
</div>