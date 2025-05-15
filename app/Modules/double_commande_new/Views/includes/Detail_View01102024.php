<div class=" table-responsive">
  <table class="table m-b-0 m-t-20">
    <tbody>
      <tr>
        <td style="width:250px ;"><font style="float:left;">&nbsp;Institution</font></td>
        <td><strong><font style="float:left;"><?= !empty($get_info['CODE_INSTITUTION']) ? $get_info['CODE_INSTITUTION'].'&nbsp;-&nbsp;' : 'N/A - ' ;?> <?= !empty($get_info['DESCRIPTION_INSTITUTION']) ? $get_info['DESCRIPTION_INSTITUTION'] : 'N/A' ;?></font></strong></td>
      </tr>
      <?php if(!empty($get_info['DESCRIPTION_SOUS_TUTEL'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_sousTitre')?></font></td>
          <td><strong><font style="float:left;"><?= !empty($get_info['CODE_SOUS_TUTEL']) ? $get_info['CODE_SOUS_TUTEL'].'&nbsp;-&nbsp;' : 'N/A - ' ;?><?= $get_info['DESCRIPTION_SOUS_TUTEL']?></font></strong></td>
        </tr>
      <?php } ?>
      <tr>
        <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_prog')?> </font></td>
        <td><strong><font style="float:left;"><?= !empty($get_info['CODE_PROGRAMME']) ? $get_info['CODE_PROGRAMME'].'&nbsp;-&nbsp;' : 'N/A - ' ;?><?=!empty($get_info['INTITULE_PROGRAMME'])?$get_info['INTITULE_PROGRAMME']:''?></font></strong></td>
      </tr>
      <tr>
        <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_action')?> </font></td>
        <td><strong><font style="float:left;"><?= !empty($get_info['CODE_ACTION']) ? $get_info['CODE_ACTION'].'&nbsp;-&nbsp;' : 'N/A - ' ;?><?= !empty($get_info['LIBELLE_ACTION'])?$get_info['LIBELLE_ACTION']:''?></font></strong></td>
      </tr>

      <?php if(!empty($get_info['CODE_NOMENCLATURE_BUDGETAIRE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.col_imputation')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['CODE_NOMENCLATURE_BUDGETAIRE']?> - <?= $get_info['LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE']?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DESC_PAP_ACTIVITE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.labelle_activites')?></font></td>
          <td><strong><font style="float:left;"><?=!empty($get_info['DESC_PAP_ACTIVITE'])?$get_info['DESC_PAP_ACTIVITE']:''?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DESC_TACHE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_taches')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['DESC_TACHE']?></font></strong></td>
        </tr>
      <?php } ?>
      
      <?php if (!empty($get_info['COMMENTAIRE'])) { ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_objet')?></font></td>
          <td><strong><font style="float:left;"><?= $num = $get_info['COMMENTAIRE'];?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if (!empty($montantvote)) { ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.labelle_credit_vote_ligne_budgetaire')?></font></td>
          <td><strong><font style="float:left;"><?= $num = number_format($montantvote,'2',',',' ');?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if (!empty($creditVote)) { ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.labelle_credit_vote_activite')?></font></td>
          <td><strong><font style="float:left;"><?= $num = number_format($creditVote,'2',',',' ');?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if (!empty($montant_reserve)) { ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.labelle_credit_reserve')?></font></td>
          <td><strong><font style="float:left;"><?= $num = number_format($montant_reserve,'2',',',' ');?></font></strong></td>
        </tr>
      <?php } ?>
      
      <!-- <tr>
        <td style="width:250px ;"><font style="float:left;"><--?=lang('messages_lang.labelle_credit_restant_apres_engagement')?></font></td>
        <td><strong><font style="float:left;"><--?= !empty(number_format($cred_act,'2',',',' ')) ? number_format($cred_act,'2',',',' ') : 0 ;?></font></strong></td>
      </tr> -->
      
      <?php if(!empty($get_info['NUMERO_BON_ENGAGEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_num_bon')?></font></td>
          <td><strong><font style="float:left;"><?= $num = (!empty($get_info['NUMERO_BON_ENGAGEMENT'])) ? $get_info['NUMERO_BON_ENGAGEMENT'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DESC_DEVISE_TYPE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_monnaie')?></font></td>
          <td><strong><font style="float:left;"><?= $num = (!empty($get_info['DESC_DEVISE_TYPE'])) ? $get_info['DESC_DEVISE_TYPE'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if($get_info['DEVISE_TYPE_ID']!=1){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_droit_taux')?></font></td>
          <td><strong><font style="float:left;"><?= $num = (!empty($get_info['COUR_DEVISE'])) ? $get_info['COUR_DEVISE'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['ENG_BUDGETAIRE_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_Montant_devise')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['ENG_BUDGETAIRE_DEVISE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['ENG_BUDGETAIRE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_Montant')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['ENG_BUDGETAIRE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['QTE_RACCROCHE'])){ $get_info['QTE_RACCROCHE']=str_replace(',','.', $get_info['QTE_RACCROCHE']);?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_quantite')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['QTE_RACCROCHE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_DEMANDE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_engaget')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_DEMANDE'])) ? date('d/m/Y',strtotime($get_info['DATE_DEMANDE'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DESCR_MARCHE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_type_marche')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DESCR_MARCHE'])) ? $get_info['DESCR_MARCHE'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DESC_TYPE_ENGAGEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_nature')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DESC_TYPE_ENGAGEMENT'])) ? $get_info['DESC_TYPE_ENGAGEMENT'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if($get_info['MARCHE_PUBLIQUE']==1){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_marche')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = 'OUI'?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if($get_info['MARCHE_PUBLIQUE']==0){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_marche')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = 'NON'?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if($get_info['EST_SOUS_TACHE']==1){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_sous_act')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = 'OUI'?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if($get_info['EST_SOUS_TACHE']==0){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_sous_act')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = 'NON'?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if($get_info['EST_FINI_TACHE']==1){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_last_act')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = 'OUI'?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if($get_info['EST_FINI_TACHE']==0){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_last_act')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = 'NON'?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['ENG_JURIDIQUE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_montant_jur')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['ENG_JURIDIQUE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['ENG_JURIDIQUE_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_montant_jur_devise')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['ENG_JURIDIQUE_DEVISE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if (!empty($get_info['DATE_ENG_JURIDIQUE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_enga')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_ENG_JURIDIQUE'])) ? date('d/m/Y',strtotime($get_info['DATE_ENG_JURIDIQUE'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if (!empty($get_info['DESC_TYPE_BENEFICIAIRE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_type_benef')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['DESC_TYPE_BENEFICIAIRE'] ;?></font></strong></td>
        </tr>
      <?php } ?>
      
      <?php if (!empty($get_info['NOM_PRESTATAIRE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_droit_prestataire')?></font></td>
          <td><strong><font style="float:left;"><?= $prestat = (!empty($get_info['NOM_PRESTATAIRE'])) ? $get_info['NOM_PRESTATAIRE'].' '.$get_info['PRENOM_PRESTATAIRE'] : $get_info['PRENOM_PRESTATAIRE'];?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DESCRIPTION_LIQUIDATION'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_type_liquidation')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['DESCRIPTION_LIQUIDATION']?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_LIQUIDATION'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_monta-liq')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_LIQUIDATION'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_LIQUIDATION_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_monta_liq_devise')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_LIQUIDATION_DEVISE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_LIQUIDATION'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_ate_liq')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_LIQUIDATION'])) ? date('d/m/Y',strtotime($get_info['DATE_LIQUIDATION'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['MONTANT_ORDONNANCEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_ord')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_ORDONNANCEMENT'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_ORDONNANCEMENT_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_ord_devise')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_ORDONNANCEMENT_DEVISE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_ORDONNANCEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_ordo')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_ORDONNANCEMENT'])) ? date('d/m/Y',strtotime($get_info['DATE_ORDONNANCEMENT'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['NUMERO_TITRE_DECAISSEMNT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_num_titre')?></font></td>
          <td><strong><font style="float:left;"><?= $num = (!empty($get_info['NUMERO_TITRE_DECAISSEMNT'])) ? $get_info['NUMERO_TITRE_DECAISSEMNT'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>      
      <?php if(!empty($get_info['NOM_PERSONNE_RETRAT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.labelle_observartion')?></font></td>
          <td><strong><font style="float:left;"><?= $num = (!empty($get_info['NOM_PERSONNE_RETRAT'])) ? $get_info['NOM_PERSONNE_RETRAT'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DESC_DEVISE_TYPE_DEC'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.labelle_devise_dec')?></font></td>
          <td><strong><font style="float:left;"><?= $num = (!empty($get_info['DESC_DEVISE_TYPE_DEC'])) ? $get_info['DESC_DEVISE_TYPE_DEC'] : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      
      <?php if(!empty($get_info['MONTANT_PAIEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_paiement')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_PAIEMENT'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_PAIEMENT_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_paiement_devise')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_PAIEMENT_DEVISE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DATE_PAIEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_paie')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_PAIEMENT'])) ? date('d/m/Y',strtotime($get_info['DATE_PAIEMENT'])) : 'N/A' ;?></font></strong></td>
        </tr>                                  
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_DECAISSEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_dec')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_DECAISSEMENT'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_DECAISSEMENT_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_dec_devise')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_DECAISSEMENT_DEVISE'],'2',',',' ')?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_DECAISSENMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_dec')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_DECAISSENMENT'])) ? date('d/m/Y',strtotime($get_info['DATE_DECAISSENMENT'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_PRISE_CHARGE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_pri')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_PRISE_CHARGE'])) ? date('d/m/Y',strtotime($get_info['DATE_PRISE_CHARGE'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_SIGNATURE_TD_MINISTRE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_sign')?></font></td>
          <td><strong><font style="float:left;"><?= $retVal = (!empty($get_info['DATE_SIGNATURE_TD_MINISTRE'])) ? date('d/m/Y',strtotime($get_info['DATE_SIGNATURE_TD_MINISTRE'])) : 'N/A' ;?></font></strong></td>
        </tr>
      <?php } ?>
   <!--    <--?php if(!empty($get_info['MONTANT_EN_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><--?=lang('messages_lang.label_devise')?></font></td>
          <td><strong><font style="float:left;"><--?= $get_info['MONTANT_EN_DEVISE']?></font></strong></td>
        </tr>
      <--?php } ?> -->
      <?php if(!empty($get_info['NOM_BANQUE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_banque')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['COMPTE_BANCAIRE'].' - '.$get_info['NOM_BANQUE']?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MOTIF_LIQUIDATION'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_prog')?>&nbsp;Motif&nbsp;liquidation</font></td>
          <td><strong><font style="float:left;"><?= $get_info['MOTIF_LIQUIDATION']?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MOTIF_PAIEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_moif')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['MOTIF_PAIEMENT']?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_APPROBATION_CONTRAT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_appro')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_APPROBATION_CONTRAT']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_sign_d')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_sign_dg')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_TENUE_ATELIER'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_tenu')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_TENUE_ATELIER']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_PRODUCTION_PROJET_LOI'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_pro')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_PRODUCTION_PROJET_LOI']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MOTANT_FACTURE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_mont_fac')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['MOTANT_FACTURE'];?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_RAPPROCHEMENT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_rapro')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_RAPPROCHEMENT']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_PRODUCTION_BALANCE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_prog')?>&nbsp;Date&nbsp;de&nbsp;production&nbps;balance</font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_PRODUCTION_BALANCE']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_PRODUCTION_COMPTE_RESULTAT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_pro')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_PRODUCTION_COMPTE_RESULTAT']));?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['DATE_INTEGRATION_OBSERVATION'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_date_int')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_INTEGRATION_OBSERVATION']));?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DATE_ELABORATION_TD'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_elaboration')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_ELABORATION_TD']));?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DATE_LIVRAISON_CONTRAT'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_date_livraison')?></font></td>
          <td><strong><font style="float:left;"><?= date('d/m/Y',strtotime($get_info['DATE_LIVRAISON_CONTRAT']));?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['MONTANT_PRELEVEMENT_FISCALES'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_montant_pre')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['MONTANT_PRELEVEMENT_FISCALES'];?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['COUT_DEVISE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.label_prog')?>&nbsp;cout&nbsp;devise</font></td>
          <td><strong><font style="float:left;"><?= $get_info['COUT_DEVISE'];?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if (!empty($get_info['EXONERATION'])){
      if($get_info['EXONERATION']==1){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_exo')?></font></td>
          <td><strong><font style="float:left;">OUI</font></strong></td>
        </tr>
      <?php } ?>
      <?php
        if($get_info['EXONERATION']==0){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_exo')?></font></td>
          <td><strong><font style="float:left;">NON</font></strong></td>
        </tr>
      <?php }} ?>
      <?php if(!empty($get_info['DESCRIPTION_TAUX_TVA'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_taux')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['DESCRIPTION_TAUX_TVA'];?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['TITRE_CREANCE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_titre_c')?></font></td>
          <td><strong><font style="float:left;"><?= $get_info['TITRE_CREANCE'];?></font></strong></td>
        </tr>
      <?php } ?>
      <?php if(!empty($get_info['MONTANT_CREANCE'])){ ?>
        <tr>
          <td style="width:250px ;"><font style="float:left;"><?=lang('messages_lang.table_Mont')?></font></td>
          <td><strong><font style="float:left;"><?= number_format($get_info['MONTANT_CREANCE'],'2',',',' ');?></font></strong></td>
        </tr>
      <?php } ?>

      <?php if(!empty($get_info['DESC_BUDGETAIRE_TYPE_DOCUMENT'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.th_type_document')?></strong></td>
          <td><strong><font style="float:left;"><?= $get_info['DESC_BUDGETAIRE_TYPE_DOCUMENT'];?></font></strong></td>
        </tr>
      <?php }?>

      <!-- retenu -->

      <?php if($get_info['TYPE_ENGAGEMENT_ID'] == 1){
      ?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_retenue')?></strong></td>
          <td class="row">
            <?php
              foreach($get_info['retenues'] as $retenue){
            ?>
            <li class="col-12 row">
              <strong class="col">
                <font>
                  <?= $retenue->CODE_RETENU;?>
                </font>
              </strong>
              <strong class="col">
                <font>
                  <?= $retenue->MONTANT_RETENU;?>
                </font>
              </strong>
            </li>
          <?php }?>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_LETTRE_OTB'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_note')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(21)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_BON_COMMANDE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.bon')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(24)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_LETTRE_COMMANDE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.lettre_commande')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(25)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_CONTRAT_JURIDIQUE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.contrat_juridique')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(26)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_BON_ENGAGEMENT'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_bon')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(1)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_TITRE_DECAISSEMENT'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.labelle_titre_dec')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(2)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_PV_ATTRIBUTION'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.pv_attribution')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(3)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_LETTRE_TRANSMISSION'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_lettre')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(20)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_PPM'])){?>
        <tr>
          <td>&nbsp;<strong>&nbsp;PPM</strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(4)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>      
      
      <?php if(!empty($get_info['PATH_LISTE_PAIE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_liste')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(5)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_AVIS_DNCMP'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_avis')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(22)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PIECE_DET']) AND !empty($get_info['PIECE_SUPPL'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.piec_just')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(23)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }
      if(!empty($get_info['PIECE_DET']) AND empty($get_info['PIECE_SUPPL'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.piec_just')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(23)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php } 
      if(!empty($get_info['PIECE_SUPPL']) AND empty($get_info['PIECE_DET'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.piec_just')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(23)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php } ?>
      
      <?php if(!empty($get_info['PATH_CONTRAT'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.contrat_approuve')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(6)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_FACTURE_LIQUIDATION'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_facture')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(17)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_PV_RECEPTION_LIQUIDATION'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_pv_reception')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(18)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>

      <?php if(!empty($get_info['PATH_PV_ATELIER'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.pv_atelier')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(7)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_PROJET_LOI'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.projet_loi')?>&nbsp;Projet&nbsp;loi</strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(8)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_CLASSIFICATION_ECONOMIQUE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_prog')?>&nbsp;Classification&nbsp;économique</strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(9)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_CLASSIFICATION_ADMINISTRATIVE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_prog')?>&nbsp;Classification&nbsp;Administrative</strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(10)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_VENTILATION_RECETTE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.ventilation_recette')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(11)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_PV_RECEPTION'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_pv_reception')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(12)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_BORDEREAU_PV_RECEPTION'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.table_bordereau')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(13)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_FACTURE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_facture')?>&nbsp;Facture</strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(14)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_RAPPORT_COMPTE_GENERAL_TRESOR'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.table_rapport_tresor')?></strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(15)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
      <?php if(!empty($get_info['PATH_BALANCE_COMPTE'])){?>
        <tr>
          <td>&nbsp;<strong><?=lang('messages_lang.label_prog')?>&nbsp;Balance&nbsp;compte</strong></td>
          <td>
            <button style="border:none;" type="button" onclick="get_doc(16)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
          </td>
        </tr>
      <?php }?>
    </tbody>
  </table>        
</div>


<!-- Éléments pour afficher le document PDF dans une fenêtre modale -->
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <?php if (!empty($get_info['PATH_BON_ENGAGEMENT'])) { ?>
          <embed id="pdf1" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_BON_ENGAGEMENT'])?>" type="application/pdf" width="100%" height="600px">
          <?php } ?>
          <?php if (!empty($get_info['PATH_TITRE_DECAISSEMENT'])) { ?>
            <embed id="pdf2" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_TITRE_DECAISSEMENT'])?>" type="application/pdf" width="100%" height="600px">
            <?php } ?>
            <?php if (!empty($get_info['PATH_PV_ATTRIBUTION'])) { 
              ?>
              <embed id="pdf3" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_PV_ATTRIBUTION'])?>" type="application/pdf" width="100%" height="600px">
              <?php } ?>
              <?php if (!empty($get_info['PATH_LETTRE_TRANSMISSION'])) { ?>
                <embed id="pdf20" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_LETTRE_TRANSMISSION'])?>" type="application/pdf" width="100%" height="600px">
                <?php } ?>
              <?php if (!empty($get_info['PATH_PPM'])) { ?>
                <embed id="pdf4" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_PPM'])?>" type="application/pdf" width="100%" height="600px">
                <?php } ?>

                <?php if (!empty($get_info['PATH_LETTRE_OTB'])) { ?>
                <embed id="pdf21" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_LETTRE_OTB'])?>" type="application/pdf" width="100%" height="600px">
                <?php } ?>

                <?php if (!empty($get_info['PATH_LISTE_PAIE'])) { ?>
                  <embed id="pdf5" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_LISTE_PAIE'])?>" type="application/pdf" width="100%" height="600px">
                  <?php } ?>
                  <?php if (!empty($get_info['PATH_AVIS_DNCMP'])) { ?>
                  <embed id="pdf22" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_AVIS_DNCMP'])?>" type="application/pdf" width="100%" height="600px">
                  <?php } ?>
                  <?php if (!empty($get_info['PATH_CONTRAT'])) { ?>
                    <embed id="pdf6" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_CONTRAT'])?>" type="application/pdf" width="100%" height="600px">
                    <?php } ?>
                    <?php if (!empty($get_info['PATH_PV_ATELIER'])) { ?>
                      <embed id="pdf7" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_PV_ATELIER'])?>" type="application/pdf" width="100%" height="600px">
                      <?php } ?>
                      <?php if (!empty($get_info['PATH_PROJET_LOI'])) { ?>
                        <embed id="pdf8" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_PROJET_LOI'])?>" type="application/pdf" width="100%" height="600px">
                        <?php } ?>
                        <?php if (!empty($get_info['PATH_CLASSIFICATION_ECONOMIQUE'])) { ?>
                          <embed id="pdf9" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_CLASSIFICATION_ECONOMIQUE'])?>" type="application/pdf" width="100%" height="600px">
                          <?php } ?>
                          <?php if (!empty($get_info['PATH_CLASSIFICATION_ADMINISTRATIVE'])) { ?>
                            <embed id="pdf10" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_CLASSIFICATION_ADMINISTRATIVE'])?>" type="application/pdf" width="100%" height="600px">
                            <?php } ?>
                            <?php if (!empty($get_info['PATH_VENTILATION_RECETTE'])) { ?>
                              <embed id="pdf11" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_VENTILATION_RECETTE'])?>" type="application/pdf" width="100%" height="600px">
                              <?php } ?>
                              <?php if (!empty($get_info['PATH_PV_RECEPTION'])) { ?>
                                <embed id="pdf12" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_PV_RECEPTION'])?>" type="application/pdf" width="100%" height="600px">
                                <?php } ?>
                                <?php if (!empty($get_info['PATH_BORDEREAU_PV_RECEPTION'])) { ?>
                                  <embed id="pdf13" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_BORDEREAU_PV_RECEPTION'])?>" type="application/pdf" width="100%" height="600px">
                                  <?php } ?>
                                  <?php if (!empty($get_info['PATH_FACTURE'])) { ?>
                                    <embed id="pdf14" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_FACTURE'])?>" type="application/pdf" width="100%" height="600px">
                                    <?php } ?>
                                    <?php if (!empty($get_info['PATH_RAPPORT_COMPTE_GENERAL_TRESOR'])) { ?>
                                      <embed id="pdf15" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_RAPPORT_COMPTE_GENERAL_TRESOR'])?>" type="application/pdf" width="100%" height="600px">
                                      <?php } ?>
                                      <?php if (!empty($get_info['PATH_BALANCE_COMPTE'])) { ?>
                                        <embed id="pdf16" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_BALANCE_COMPTE'])?>" type="application/pdf" width="100%" height="600px">
                                          <?PHP } ?>
                                          
                                          <?php if (!empty($get_info['PATH_FACTURE_LIQUIDATION'])) { ?>
                                            <embed id="pdf17" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_FACTURE_LIQUIDATION'])?>" type="application/pdf" width="100%" height="600px">
                                          <?php } ?>

                                          <?php if (!empty($get_info['PATH_PV_RECEPTION_LIQUIDATION'])) { ?>
                                            <embed id="pdf18" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_PV_RECEPTION_LIQUIDATION'])?>" type="application/pdf" width="100%" height="600px">
                                          <?php } ?>

                                          <?php if (!empty($get_info['PATH_BON_COMMANDE'])) { ?>
                                            <embed id="pdf24" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_BON_COMMANDE'])?>" type="application/pdf" width="100%" height="600px">
                                          <?php } ?>

                                          <?php if (!empty($get_info['PATH_LETTRE_COMMANDE'])) { ?>
                                            <embed id="pdf25" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_LETTRE_COMMANDE'])?>" type="application/pdf" width="100%" height="600px">
                                          <?php } ?>

                                          <?php if (!empty($get_info['PATH_CONTRAT_JURIDIQUE'])) { ?>
                                            <embed id="pdf26" style="display:none;" src="<?=base_url('uploads/double_commande_new/'.$get_info['PATH_CONTRAT_JURIDIQUE'])?>" type="application/pdf" width="100%" height="600px">
                                          <?php } ?>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <script type="text/javascript">
                                    function get_doc(doc){
                                      if (doc==1) {
                                        $('#pdf1').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf1').css('display', 'none');
                                      }
                                      if (doc==2) {
                                        $('#pdf2').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf2').css('display', 'none');
                                      }
                                      if (doc==3) {
                                        $('#pdf3').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf3').css('display', 'none');
                                      }
                                      if (doc==4) {
                                        $('#pdf4').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf4').css('display', 'none');
                                      }
                                      if (doc==21) {
                                        $('#pdf21').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf21').css('display', 'none');
                                      }
                                      if (doc==22) {
                                        $('#pdf22').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf22').css('display', 'none');
                                      }
                                      if (doc==20) {
                                        $('#pdf20').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf20').css('display', 'none');
                                      }
                                      if (doc==23) {
                                        $('#pdf23').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf23').css('display', 'none');
                                      }
                                      if (doc==5) {
                                        $('#pdf5').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf5').css('display', 'none');
                                      }
                                      if (doc==6) {
                                        $('#pdf6').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf6').css('display', 'none');
                                      }
                                      if (doc==7) {
                                        $('#pdf7').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf7').css('display', 'none');
                                      }
                                      if (doc==8) {
                                        $('#pdf8').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf8').css('display', 'none');
                                      }
                                      if (doc==9) {
                                        $('#pdf9').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf9').css('display', 'none');
                                      }
                                      if (doc==10) {
                                        $('#pdf10').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf10').css('display', 'none');
                                      }
                                      if (doc==11) {
                                        $('#pdf11').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf11').css('display', 'none');
                                      }
                                      if (doc==12) {
                                        $('#pdf12').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf12').css('display', 'none');
                                      }
                                      if (doc==13) {
                                        $('#pdf13').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#pdf13').css('display', 'none');
                                      }
                                      if (doc==14) {
                                        $('#pdf14').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf14').css('display', 'none');
                                      }
                                      if (doc==15) {
                                        $('#pdf15').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf15').css('display', 'none');
                                      }
                                      if (doc==16) {
                                        $('#pdf16').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf16').css('display', 'none');
                                      }
                                      if (doc==17) {
                                        $('#pdf17').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf17').css('display', 'none');
                                      }
                                      if (doc==18) {
                                        $('#pdf18').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf18').css('display', 'none');
                                      }
                                      if (doc==24) {
                                        $('#pdf24').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf24').css('display', 'none');
                                      }
                                      if (doc==25) {
                                        $('#pdf25').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf25').css('display', 'none');
                                      }
                                      if (doc==26) {
                                        $('#pdf26').css('display', 'block');
                                        $('#modal').modal('show');      
                                      }else{
                                        $('#modal').modal('hide');
                                        $('#pdf26').css('display', 'none');
                                      }
                                    }
                                  </script>

