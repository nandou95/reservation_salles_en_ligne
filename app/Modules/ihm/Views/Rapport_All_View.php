<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>

  <style type="text/css">
    .card {
  --bs-card-spacer-y: 1.35rem;
  --bs-card-spacer-x: 1.35rem;
  --bs-card-title-spacer-y: 0.5rem;
  --bs-card-border-width: 1px;
  --bs-card-border-color: rgba(33, 40, 50, 0.125);
  --bs-card-border-radius: 0.35rem;
  --bs-card-box-shadow: ;
  --bs-card-inner-border-radius: 0.35rem;
  --bs-card-cap-padding-y: 1rem;
  --bs-card-cap-padding-x: 1.35rem;
  --bs-card-cap-bg: rgba(33, 40, 50, 0.03);
  --bs-card-cap-color: ;
  --bs-card-height: ;
  --bs-card-color: ;
  --bs-card-bg: #fff;
  --bs-card-img-overlay-padding: 1rem;
  --bs-card-group-margin: 0.75rem;
  position: relative;
  display: flex;
  flex-direction: column;
  min-width: 0;
  height: var(--bs-card-height);
  word-wrap: break-word;
  background-color: var(--bs-card-bg);
  background-clip: border-box;
  border: var(--bs-card-border-width) solid var(--bs-card-border-color);
  border-radius: var(--bs-card-border-radius);
}
.card > hr {
  margin-right: 0;
  margin-left: 0;
}
.card > .list-group {
  border-top: inherit;
  border-bottom: inherit;
}
.card > .list-group:first-child {
  border-top-width: 0;
  border-top-left-radius: var(--bs-card-inner-border-radius);
  border-top-right-radius: var(--bs-card-inner-border-radius);
}
.card > .list-group:last-child {
  border-bottom-width: 0;
  border-bottom-right-radius: var(--bs-card-inner-border-radius);
  border-bottom-left-radius: var(--bs-card-inner-border-radius);
}
.card > .card-header + .list-group,
.card > .list-group + .card-footer {
  border-top: 0;
}

.card-body {
  flex: 1 1 auto;
  padding: var(--bs-card-spacer-y) var(--bs-card-spacer-x);
  color: var(--bs-card-color);
}

.card-title {
  margin-bottom: var(--bs-card-title-spacer-y);
}

.card-subtitle {
  margin-top: calc(-0.5 * var(--bs-card-title-spacer-y));
  margin-bottom: 0;
}

.card-text:last-child {
  margin-bottom: 0;
}

.card-link:hover {
  text-decoration: none;
}
.card-link + .card-link {
  margin-left: var(--bs-card-spacer-x);
}

.card-header {
  padding: var(--bs-card-cap-padding-y) var(--bs-card-cap-padding-x);
  margin-bottom: 0;
  color: var(--bs-card-cap-color);
  background-color: var(--bs-card-cap-bg);
  border-bottom: var(--bs-card-border-width) solid var(--bs-card-border-color);
}
.card-header:first-child {
  border-radius: var(--bs-card-inner-border-radius) var(--bs-card-inner-border-radius) 0 0;
}

.card-footer {
  padding: var(--bs-card-cap-padding-y) var(--bs-card-cap-padding-x);
  color: var(--bs-card-cap-color);
  background-color: #c0c0c0;
  border-top: var(--bs-card-border-width) solid #c0c0c0;;
}
.card-footer:last-child {
  border-radius: 0 0 var(--bs-card-inner-border-radius) var(--bs-card-inner-border-radius);
}

.card-header-tabs {
  margin-right: calc(-0.5 * var(--bs-card-cap-padding-x));
  margin-bottom: calc(-1 * var(--bs-card-cap-padding-y));
  margin-left: calc(-0.5 * var(--bs-card-cap-padding-x));
  border-bottom: 0;
}
.card-header-tabs .nav-link.active {
  background-color: var(--bs-card-bg);
  border-bottom-color: var(--bs-card-bg);
}

.card-header-pills {
  margin-right: calc(-0.5 * var(--bs-card-cap-padding-x));
  margin-left: calc(-0.5 * var(--bs-card-cap-padding-x));
}

.card-img-overlay {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  padding: var(--bs-card-img-overlay-padding);
  border-radius: var(--bs-card-inner-border-radius);
}

.card-img,
.card-img-top,
.card-img-bottom {
  width: 100%;
}

.card-img,
.card-img-top {
  border-top-left-radius: var(--bs-card-inner-border-radius);
  border-top-right-radius: var(--bs-card-inner-border-radius);
}

.card-img,
.card-img-bottom {
  border-bottom-right-radius: var(--bs-card-inner-border-radius);
  border-bottom-left-radius: var(--bs-card-inner-border-radius);
}

.card-group > .card {
  margin-bottom: var(--bs-card-group-margin);
}
@media (min-width: 576px) {
  .card-group {
    display: flex;
    flex-flow: row wrap;
  }
  .card-group > .card {
    flex: 1 0 0%;
    margin-bottom: 0;
  }
  .card-group > .card + .card {
    margin-left: 0;
    border-left: 0;
  }
  .card-group > .card:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
  .card-group > .card:not(:last-child) .card-img-top,
  .card-group > .card:not(:last-child) .card-header {
    border-top-right-radius: 0;
  }
  .card-group > .card:not(:last-child) .card-img-bottom,
  .card-group > .card:not(:last-child) .card-footer {
    border-bottom-right-radius: 0;
  }
  .card-group > .card:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }
  .card-group > .card:not(:first-child) .card-img-top,
  .card-group > .card:not(:first-child) .card-header {
    border-top-left-radius: 0;
  }
  .card-group > .card:not(:first-child) .card-img-bottom,
  .card-group > .card:not(:first-child) .card-footer {
    border-bottom-left-radius: 0;
  }
}
</style>

<style type="text/css">
#snackbar {
  position: fixed;
    top: 60px;
    right: 38px;
    background: #bddaed;
    padding: 0.5rem;
    border-radius: 4px;
    box-shadow: -1px 1px 10px
        rgba(0, 0, 0, 0.3);
    z-index: 1023;

    width: auto;
  height: auto;
  /*overflow: auto;*/
}
</style>

<style type="text/css">
  .d-none {
  display: none !important;
}

/*.me-3 {
  margin-right: 1rem !important;
}
*/
.d-sm-block {
    display: block !important;
}

.dropdown-notifications {
  position: static;
}
.dropdown-notifications .dropdown-menu {
  padding-top: 0;
  padding-bottom: 0;
  width: calc(100% - 1.5rem);
  right: 0.75rem;
  max-height: calc(21rem - 1px);
  overflow-x: hidden;
  overflow-y: overlay;
}
.dropdown-notifications .dropdown-menu::-webkit-scrollbar {
  width: 0.75rem;
}
.dropdown-notifications .dropdown-menu::-webkit-scrollbar-thumb {
  border-radius: 10rem;
  border-width: 0.2rem;
  border-style: solid;
  background-clip: padding-box;
  background-color: rgba(33, 40, 50, 0.2);
  border-color: transparent;
}
.dropdown-notifications .dropdown-menu::-webkit-scrollbar-button {
  width: 0;
  height: 0;
  display: none;
}
.dropdown-notifications .dropdown-menu::-webkit-scrollbar-corner {
  background-color: transparent;
}
.dropdown-notifications .dropdown-menu::-webkit-scrollbar-track {
  background: inherit;
}
@media (pointer: fine) and (hover: hover) {
  .dropdown-notifications .dropdown-menu {
    overflow-y: hidden;
  }
  .dropdown-notifications .dropdown-menu:hover {
    overflow-y: overlay;
  }
}
@media (pointer: coarse) and (hover: none) {
  .dropdown-notifications .dropdown-menu {
    overflow-y: overlay;
  }
}
@-moz-document url-prefix() {
  .dropdown-notifications .dropdown-menu {
    overflow-y: auto;
  }
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-header {
  background-color: #0061f2;
  color: #fff !important;
  padding-top: 1rem;
  padding-bottom: 1rem;
  line-height: 1;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-header svg {
  height: 0.7rem;
  width: 0.7rem;
  opacity: 0.7;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item {
  padding-top: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e0e5ec;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-icon,
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-img {
  height: 2.5rem;
  width: 2.5rem;
  border-radius: 100%;
  margin-right: 1rem;
  flex-shrink: 0;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-icon {
  background-color: #0061f2;
  display: flex;
  align-items: center;
  justify-content: center;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-icon svg {
  text-align: center;
  font-size: 0.85rem;
  color: #fff;
  height: 0.85rem;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-content .dropdown-notifications-item-content-details {
  color: #a7aeb8;
  font-size: 0.7rem;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-content .dropdown-notifications-item-content-text {
  font-size: 0.9rem;
  max-width: calc(100vw - 8.5rem);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-content .dropdown-notifications-item-content-actions .btn-sm, .dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-content .dropdown-notifications-item-content-actions .btn-group-sm > .btn {
  font-size: 0.7rem;
  padding: 0.15rem 0.35rem;
  cursor: pointer;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-footer {
  justify-content: center;
  font-size: 0.8rem;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  color: #a7aeb8;
  cursor: pointer;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-footer .dropdown-notifications-footer-icon {
  height: 1em;
  width: 1em;
  margin-left: 0.25rem;
}
.dropdown-notifications .dropdown-menu .dropdown-notifications-footer:active {
  color: #fff;
}
@media (min-width: 576px) {
  .dropdown-notifications {
    position: relative;
  }
  .dropdown-notifications .dropdown-menu {
    width: auto;
    min-width: 18.75rem;
    right: 0;
  }
  .dropdown-notifications .dropdown-menu .dropdown-notifications-item .dropdown-notifications-item-content .dropdown-notifications-item-content-text {
    max-width: 13rem;
  }
}

.no-caret .dropdown-toggle::after {
  display: none;
}
</style>


</head>
<body>
  <?php 

  if ($EBCORRIGE>50)
  {
    $color1 = '24c2c7';
  } 
  else if ($EBCORRIGE>=20 && $EBCORRIGE<50) 
  {
    $color1 = 'FF5733';
  }
  else if ($EBCORRIGE<20)
  {
    $color1 = '183293';
  }

  if ($EBAVALIDE>50)
  {
    $color2 = '24c2c7';
  } 
  else if ($EBAVALIDE>=20 && $EBAVALIDE<50) 
  {
    $color2 = 'FF5733';
  }
  else if ($EBAVALIDE<20)
  {
    $color2 = '183293';
  }

  if ($EJFAIRE>50)
  {
    $color3 = '24c2c7';
  } 
  else if ($EJFAIRE>=20 && $EJFAIRE<50) 
  {
    $color3 = 'FF5733';
  }
  else if ($EJFAIRE<20)
  {
    $color3 = '183293';
  }

  if ($EJCORRIGER>50)
  {
    $color4 = '24c2c7';
  } 
  else if ($EJCORRIGER>=20 && $EJCORRIGER<50) 
  {
    $color4 = 'FF5733';
  }
  else if ($EJCORRIGER<20)
  {
    $color4 = '183293';
  }

  if ($EJVALIDER>50)
  {
    $color5 = '24c2c7';
  } 
  else if ($EJVALIDER>=20 && $EJVALIDER<50) 
  {
    $color5 = 'FF5733';
  }
  else if ($EJVALIDER<20)
  {
    $color5 = '183293';
  }

  if ($LIQFAIRE>50)
  {
    $color6 = '24c2c7';
  } 
  else if ($LIQFAIRE>=20 && $LIQFAIRE<50) 
  {
    $color6 = 'FF5733';
  }
  else if ($LIQFAIRE<20)
  {
    $color6 = '183293';
  }

  if ($LIQCORRIGER>50)
  {
    $color7 = '24c2c7';
  } 
  else if ($LIQCORRIGER>=20 && $LIQCORRIGER<50) 
  {
    $color7 = 'FF5733';
  }
  else if ($LIQCORRIGER<20)
  {
    $color7 = '183293';
  }

  if ($LIQVALIDE>50)
  {
    $color8 = '24c2c7';
  } 
  else if ($LIQVALIDE>=20 && $LIQVALIDE<50) 
  {
    $color8 = 'FF5733';
  }
  else if ($LIQVALIDE<20)
  {
    $color8 = '183293';
  }

  if ($ORDVALIDE>50)
  {
    $color9 = '24c2c7';
  } 
  else if ($ORDVALIDE>=20 && $ORDVALIDE<50) 
  {
    $color9 = 'FF5733';
  }
  else if ($ORDVALIDE<20)
  {
    $color9 = '183293';
  }

  if ($prise_charge_a_recep>50)
  {
    $color10 = '24c2c7';
  } 
  else if ($prise_charge_a_recep>=20 && $prise_charge_a_recep<50) 
  {
    $color10 = 'FF5733';
  }
  else if ($prise_charge_a_recep<20)
  {
    $color10 = '183293';
  }

  if ($titre_attente_etab>50)
  {
    $color11 = '24c2c7';
  } 
  else if ($titre_attente_etab>=20 && $titre_attente_etab<50) 
  {
    $color11 = 'FF5733';
  }
  else if ($titre_attente_etab<20)
  {
    $color11 = '183293';
  }

  if ($titre_attente_corr>50)
  {
    $color12 = '24c2c7';
  } 
  else if ($titre_attente_corr>=20 && $titre_attente_corr<50) 
  {
    $color12 = 'FF5733';
  }
  else if ($titre_attente_corr<20)
  {
    $color12 = '183293';
  }

  if ($dir_compt_recep>50)
  {
    $color13 = '24c2c7';
  } 
  else if ($dir_compt_recep>=20 && $dir_compt_recep<50) 
  {
    $color13 = 'FF5733';
  }
  else if ($dir_compt_recep<20)
  {
    $color13 = '183293';
  }

  if ($obr_recep>50)
  {
    $color14 = '24c2c7';
  } 
  else if ($obr_recep>=20 && $obr_recep<50) 
  {
    $color14 = 'FF5733';
  }
  else if ($obr_recep<20)
  {
    $color14 = '183293';
  }

  if ($dec_att_trait>50)
  {
    $color15 = '24c2c7';
  } 
  else if ($dec_att_trait>=20 && $dec_att_trait<50) 
  {
    $color15 = 'FF5733';
  }
  else if ($dec_att_trait<20)
  {
    $color15 = '183293';
  }

  if ($dec_att_recep_brb>50)
  {
    $color16 = '24c2c7';
  } 
  else if ($dec_att_recep_brb>=20 && $dec_att_recep_brb<50) 
  {
    $color16 = 'FF5733';
  }
  else if ($dec_att_recep_brb<20)
  {
    $color16 = '183293';
  }
  ?>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="row">
                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_budj_corriger')?>" class="text-white"><b><?=$EBCORRIGE?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color1 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_eng_budg_wait_corr')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_budj_valide')?>" class="text-white"><b><?=$EBAVALIDE?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color2 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_eng_budg_wait_valid')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_faire')?>" class="text-white"><b><?=$EJFAIRE?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color3 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_eng_jur_wait_faire')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_corriger')?>" class="text-white"><b><?=$EJCORRIGER?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color4 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_eng_jur_wait_corr')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_valide')?>" class="text-white"><b><?=$EJVALIDER?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color5 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_eng_jur_wait_valid')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_faire')?>" class="text-white"><b><?=$LIQFAIRE?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color6 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_liquid_wait_faire')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_corrige')?>" class="text-white"><b><?=$LIQCORRIGER?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color7 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_liquid_wait_corr')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_valide')?>" class="text-white"><b><?=$LIQVALIDE?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color8 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_liquid_wait_valid')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/ordonnance_valide')?>" class="text-white"><b><?=$ORDVALIDE?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color9 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.titre_ordo_wait_faire')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/prise_charge_attente_reception')?>" class="text-white"><b><?=$prise_charge_a_recep?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color10 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_pc_recep')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_etablissement')?>" class="text-white"><b><?=$titre_attente_etab?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color11 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_titre_att_recep')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_correction')?>" class="text-white"><b><?=$titre_attente_corr?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color12 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_titre_att_corr')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_reception_dir_compt')?>" class="text-white"><b><?=$dir_compt_recep?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color13 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_titre_att_recep_dir_comptable')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/titre_attente_reception_obr')?>" class="text-white"><b><?=$obr_recep?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color14 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_titre_att_recep_obr')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/decais_attente_traitement')?>" class="text-white"><b><?=$dec_att_trait?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color15 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_titre_att_trait')?>
                            </div>
                        </div>
                      </div>

                      <div class="col-lg-6 col-xl-3 mb-4">
                        <div class="card">
                            <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
                                <a href="<?=base_url('double_commande_new/Suivi_Execution/decais_attente_recep_brb')?>" class="text-white"><b><?=$dec_att_recep_brb?></b></a>
                            </div>
                            <div style="background-color: #ecd9d5; padding: 5px;"></div>
                            <div style="background-color: #<?php echo $color16 ?>; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
                              <?=lang('messages_lang.suivi_titre_att_recep_brb')?>
                            </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="ex3">
                        <div id="snackbar">
                            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                              <a class="btn btn-icon btn-transparent-dark dropdown-toggle" href="javascript:void(0);" role="button" data-bs-toggle="dropdown"><span class="badge 007bff-soft text-primary ms-auto"><i  style="color: white;"class="fa fa-bell"></i> <b  style="color: white;" id="nbrEngagNouveau1"></b></span></a>
                              <div class="dropdown-menu">
                                  <h2 class="dropdown-header dropdown-notifications-header">
                                      <i class="me-2" data-feather="bell"></i>
                                      <b id="nbrEngagNouveau2"></b>
                                  </h2>
                                  <span id="html"></span>
                              </div>
                          </li>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
          </main>
        </div>
      </div>
 
      <?php echo view('includesbackend/scripts_js.php'); ?>
    </body>
    </html>
    <!-- <div class="toast-overlay" id="toast-overlay"></div> -->

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>


<script type="text/javascript">
$(document).ready(function()
{
  get_engag_nouveau();
});

function get_statut(EXECUTION_BUDGETAIRE_ID)
{
  $.ajax(
  { 
    url: '<?=base_url('/ihm/Rapport_All/get_statut')?>',
    type:"POST",
    dataType:"JSON",
    data: {EXECUTION_BUDGETAIRE_ID:EXECUTION_BUDGETAIRE_ID},
    success:function(data)
    {
      if (data.status)
      {
        get_engag_nouveau()
      }
    }
  });
}

function get_engag_nouveau()
{
  $.ajax(
  { 
    url: '<?=base_url('/ihm/Rapport_All/get_engag_nouveau')?>',
    type:"POST",
    dataType:"JSON",
    data: {  },
    success:function(data)
    {
      // alert(data.nbr)
      $('#nbrEngagNouveau1').text(data.nbr+' '+'Nouveaux engagements')
      $('#nbrEngagNouveau2').text(data.nbr+' '+'Nouveaux engagements')
      $('#html').html(data.html)

      setTimeout(()=>{
        get_statut()
      },5000)
    }
  });
}
</script>
  