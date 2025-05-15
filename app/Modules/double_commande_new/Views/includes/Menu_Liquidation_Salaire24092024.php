<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">

    <?php
    // if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB')==1)
    // {
      echo '<a href="'.base_url('double_commande_new/Liquidation_Salaire_Liste/index_A_Corr').'" class="'.$corrige_liqu_salaire.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.liquid_salaire_corr').'</p></div> <div class="menu-link"><span>'.$nbr_liqu_salaire.'</span></div></a>';

      echo '<a href="'.base_url('double_commande_new/Liquidation_Salaire_Liste/index_A_valider').'" class="'.$liq_a_valide.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.liquid_salaire_valider').'</p></div> <div class="menu-link"><span>'.$nbr_liq_a_valide.'</span></div></a>';

      echo '<a href="'.base_url('double_commande_new/Liquidation_Salaire_Liste/index_Deja_valider').'" class="'.$liq_deja_valide.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.liquid_salaire_deja_valider').'</p></div> <div class="menu-link"><span>'.$nbr_liq_deja_valide.'</span></div></a>';
      
    // }
    ?>
  </div>
</div>