
<div class="table-responsive" style="width: 100%;">
  <div class="d-flex justify-content-between align-items-end">
    <div class="col-lg-5 col-xl-4 mb-4">
      <div class="card">
        <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
          <a href="#" class="text-black" id='div_engag_budj'><b><?=$ENG_BUDG?></b>BIF</a>
        </div>
        <div style="background-color: #8c564b; padding: 5px;"></div>
        <div style="background-color: #39737c; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
          <?=lang('messages_lang.label_engage')?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-4 mb-4">
      <div class="card">
        <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
          <a href="#" class="text-black" id='div_engag_jur'><b><?=$ENG_JURD?>BIF</b></a>
        </div>
        <div style="background-color: #8c564b; padding: 5px;"></div>
        <div style="background-color: #39737c; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
          <?=lang('messages_lang.label_mont_juridique')?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-3 mb-4">
      <div class="card">
        <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
          <a href="#" class="text-black" id='div_liqui'><b><?=$LIQUIDATION?></b>BIF</a>
        </div>
        <div style="background-color: #8c564b; padding: 5px;"></div>
        <div style="background-color: #39737c; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
          <?=lang('messages_lang.lab_mont_liquid')?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-3 mb-4">
      <div class="card">
        <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
          <a href="#" class="text-black" id='div_ordo'><b><?=$ORDONNANCEMENT?></b>BIF</a>
        </div>
        <div style="background-color: #8c564b; padding: 5px;"></div>
        <div style="background-color: #39737c; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
          <?=lang('messages_lang.table_mont_ord')?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-3 mb-4">
      <div class="card">
        <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
          <a href="#" class="text-black" id='div_paiem'><b><?=$PAIEMENT?>BIF</b></a>
        </div>
        <div style="background-color: #8c564b; padding: 5px;"></div>
        <div style="background-color: #39737c; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
          <?=lang('messages_lang.table_mont_paiement')?>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-xl-3 mb-4">
      <div class="card">
        <div style="background-color: #c0c0c0; padding: 5px;border-radius: 5px 5px 0px 0px; text-align: center; font-size: 30px;">
          <a href="#" class="text-black" id='div_dec'><b><?=$DECAISSEMENT?>BIF</b></a>
        </div>
        <div style="background-color: #8c564b; padding: 5px;"></div>
        <div style="background-color: #39737c; padding: 30px;border-radius: 0px 0px 5px 5px;color:white;">
          <?=lang('messages_lang.table_mont_dec')?>
        </div>
      </div>
    </div>

    <!-- <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_budj_corriger')?>" class="<?=$eng_budg_a_corriger?> btn-menu">
      <div class="btn-menu-text">
        <p class="menu-text"><?=lang('messages_lang.label_engage')?></p>
      </div>
      <div class="menu-link" id='div_engag_budj'>
        <span><?=$ENG_BUDG?></span>
      </div>
    </a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_budj_valide')?>" class="<?=$eng_budg_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.label_mont_juridique')?></p></div> <div class="menu-link" id='div_engag_jur'><span><?=$ENG_JURD?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_faire')?>" class="<?=$eng_jurd_a_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.lab_mont_liquid')?></p></div> <div class="menu-link" id='div_liqui'><span><?=$LIQUIDATION?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_corriger')?>" class="<?=$eng_jurd_a_corriger?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.table_mont_ord')?></p></div> <div class="menu-link" id='div_ordo'><span><?=$ORDONNANCEMENT?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/engag_jurd_valide')?>" class="<?=$eng_jurd_a_valider?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.table_mont_paiement')?></p></div> <div class="menu-link" id='div_paiem'><span><?=$PAIEMENT?></span></div></a>

    <a href="<?=base_url('double_commande_new/Suivi_Execution/liquidation_faire')?>" class="<?=$liq_a_faire?> btn-menu"><div class="btn-menu-text"> <p class="menu-text"><?=lang('messages_lang.table_mont_dec')?></p></div> <div class="menu-link" id='div_dec'><span><?=$DECAISSEMENT?></span></div></a> -->

  </div>
</div>