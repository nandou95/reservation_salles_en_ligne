<?php 
$session  = \Config\Services::session();
$profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

?>
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">
    <?php
    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE')==1)
    {
      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_prise_charge').'" class="'.$prise_charge_salaire.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.pris_charg_salaire').'</p></div> <div class="menu-link"><span>'.$nbr_prise_charge_salaire.'</span></div></a>';
    }

    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT')==1){
      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_td_Salaire_Net').'" class="'.$class_td_Salaire_Net.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.td_salaire_net').'  </p></div> <div class="menu-link"><span>'.$nbre_td_net.'</span></div></a>';

      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_td_Autres_Retenus').'" class="'.$class_td_Autres_Retenus.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.td_salaire_autres_retenu').'  </p></div> <div class="menu-link"><span>'.$nbre_td_autr_ret.'</span></div></a>';

    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE')==1)
    {

      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_sign_dir_compt').'" class="'.$sign_dir_comp.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.label_titre_dir_compt').'</p></div> <div class="menu-link"><span>'.$sign_dir_compt.'</span></div></a>';
    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP')==1)
    {
      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_sign_dgfp').'" class="'.$sign_dir_dgfp.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.label_titre_dgfp').'</p></div> <div class="menu-link"><span>'.$sign_dgfp.'</span></div></a>';
    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_MINISTRE')==1)
    {
      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_sign_ministre').'" class="'.$sign_dir_min.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.label_titre_min').'</p></div> <div class="menu-link"><span>'.$sign_min.'</span></div></a>';

    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD')==1)
    {
      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_net').'" class="'.$class_valid_td_net.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.valid_td_net').'  </p></div> <div class="menu-link"><span>'.$valid_td_net.'</span></div></a>';

      echo '<a href="'.base_url('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_autre_retenu').'" class="'.$class_valid_td_autr_ret.' btn-menu"><div class="btn-menu-text"> <p class="menu-text">'.lang('messages_lang.valid_td_autre').'  </p></div> <div class="menu-link"><span>'.$valid_td_autr_ret.'</span></div></a>';
    }


    ?>

  </div>
</div>