<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">
    <?php
    if($profil_id==8 || $profil_id==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Liste_Decaissement')?>" class="<?=$decaissement_a_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_dec_fer')?></p></div> <div class="menu-link"><span><?=$decais_a_faire;?></span></div></a>
      <?php
    }
    ?>

    <?php 
    if($profil_id==8 || $profil_id==9 || $profil_id==10 || $profil_id==4 || $profil_id==1)
    {
      ?>
      <a href="<?=base_url('double_commande_new/Liste_Decaissement_Deja_Fait')?>" class="<?=$decaissement_deja_fait?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_dec_dejfet')?></p></div> <div class="menu-link"><span><?=$decais_deja_fait;?></span></div></a>
      <?php
    }
    ?>


    <?php
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB')==1)
    {
    ?>

    <a href="<?=base_url('double_commande_new/Reception_BRB/liste_vue')?>" class="<?=$recepion_brb?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_recep_brb')?></p></div> <div class="menu-link"><span><?=$recep_brb?></span></div></a>

    <a href="<?=base_url('double_commande_new/Transmission_Deja_Reception_BRB/liste_trans_rec_vue')?>" class="<?=$deja_reception_brb?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.menu_deja_recep_brb')?></p></div> <div class="menu-link"><span><?=$déjà_recep_brb?></span></div></a>
    <?php
        }
        ?>
  </div>
</div>