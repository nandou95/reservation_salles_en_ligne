<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">

    <?php
    // if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB')==1)
    // {
      echo '<a href="'.base_url('double_commande_new/Ordonnancement_Salaire_Liste/index_A_Faire').'" class="'.$corrige_ordo_salaire.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.titre_ordo_salaire_faire').'</p></div> <div class="menu-link"><span>'.$nbr_ordo_salaire.'</span></div></a>';

      echo '<a href="'.base_url('double_commande_new/Ordonnancement_Salaire_Liste/index_Deja_Fait').'" class="'.$ordo_deja_fait.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.titre_ordo_salaire_deja').'</p></div> <div class="menu-link"><span>'.$nbr_ordo_deja_fait.'</span></div></a>';
    // }
    ?>
  </div>
</div>