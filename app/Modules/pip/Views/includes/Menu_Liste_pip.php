<div class="d-flex justify-content-between align-items-end">
	<a href="<?=base_url('pip/Projet_Pip_Infini/liste_pip_infini')?>" class="<?=$incomplet?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_projet_incomplet') ?></p></div><div class="menu-link"><span><?=$nbre_incomplet?></span></div></a>

	<a href="<?=base_url('pip/Projet_Pip_Fini/liste_pip_fini')?>" class="<?=$Complet?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_projet_transmis') ?></p></div><div class="menu-link"><span><?=$nbre_Complet?></span></div></a>

	<a href="<?=base_url('pip/Projet_Pip_Corrige/liste_pip_corrige')?>" class="<?=$corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_projet_corriger') ?></p></div><div class="menu-link"><span><?=$nbre_corriger?></span></div></a>

	<a href="<?=base_url('pip/Projet_Pip_Valide/liste_pip_valide')?>" class="<?=$valide?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?= lang('messages_lang.titre_projet_valide') ?></p></div><div class="menu-link"><span><?=$nbre_valide?></span></div></a>
</div>