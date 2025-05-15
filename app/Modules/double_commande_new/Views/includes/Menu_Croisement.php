<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">

    <?php
       // if(SESSION){
      ?>

      <a href="<?=base_url('double_commande_new/Liste_ptba_orginal')?>" class="<?=$ptba_original?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Liste des taches ptba</p></div> <div class="menu-link"><span><?=$nbre_tache?></span></div></a>

      
      <a href="<?=base_url('double_commande_new/Liste_ptba_revise')?>" class="<?=$ptba_revise?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Liste des taches Revisées</p></div> <div class="menu-link"><span><?=$nbre_tache_revise?></span></div></a>

      <a href="<?=base_url('double_commande_new/Liste_tache_trouve')?>" class="<?=$taches_trouve?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Liste des taches trouvées</p></div> <div class="menu-link"><span><?=$nbre_tache_trouves?></span></div></a>
      
      <a href="<?=base_url('double_commande_new/Liste_croisement_ptba_ptba_revise')?>" class="<?=$taches_non_trouve?> btn-menu"><div class="btn-menu-text"> <p class="menu-text">Liste des taches non trouvées</p></div> <div class="menu-link"><span><?=$nbre_tache_non_trouves?></span></div></a>
      <?php
         // } fin condition session
      ?>


    


  </div>
</div>