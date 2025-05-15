<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">

    <?php
    if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT')==1)
    {
      ?>

      <a href="<?=base_url('double_commande_new/Decaissement_Salaire_Liste/vue_decaiss_faire')?>" class="<?=$decaissFaire_class?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.decaisement_a_faire')?></p></div> <div class="menu-link"><span><?=$nbre_decaiss_faire?></span></div></a>

      
      <a href="<?=base_url('double_commande_new/Decaissement_Salaire_Liste/vue_decaiss_faits')?>" class="<?=$Decaiss_deja_fait_class?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.decaisement_Faits')?></p></div> <div class="menu-link"><span><?=$nbre_decaiss_Fait?></span></div></a>
      <?php
    }
    ?>


    


  </div>
</div>