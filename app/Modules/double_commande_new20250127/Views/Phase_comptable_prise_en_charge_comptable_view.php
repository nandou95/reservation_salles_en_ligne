<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo view('includesbackend/header.php'); ?>
    <?php $validation = \Config\Services::validation();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

    if (empty($user_id)) {
        return redirect('Login_Ptba');
    }
    ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <style type="text/css">
        .modal-signature {
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            border-bottom-right-radius: .3rem;
            border-bottom-left-radius: .3rem
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php echo view('includesbackend/navybar_menu.php'); ?>

        <div class="main">
            <?php echo view('includesbackend/navybar_topbar.php'); ?>
            <main class="content">

                <div class="container-fluid">

                    <div class="row" style="margin-top: -5px">
                        <div class="col-12">
                            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                                <div class="card-header">

                                </div>

                                <div class="card-body">

                                    <?php
                                    if (session()->getFlashKeys('alert')) {
                                        ?>
                                        <div class="alert alert-danger" id="message">
                                            <?php echo session()->getFlashdata('alert')['message']; ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <div style="margin-top: -25px;" class="card">
                                    </div>
                                    <div class="card-body" style="margin-top: -20px">

                                        <div style="float: right;">
                                            <a href="<?php echo base_url('double_commande_new/Liste_Paiement') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i>
                                                <?= lang('messages_lang.list_phase_comptable_prise_en_charge') ?>
                                            </a>
                                        </div>
                                        <div>
                                            <h4>
                                                <font style="font-size:18px">
                                                    <?= lang('messages_lang.phase_comptable_phase_comptable_prise_en_charge') ?> : <?php if (!empty($etapes)) { ?>
                                                        <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                                                    <?php    } ?>

                                                </font>
                                            </h4>
                                        </div>
                                        <hr>
                                        <!-- debut -->
                                        <div class="" style="width:90%">
                                            <div id="accordion">
                                                <div class="card-header" id="headingThree" style="padding: 0; display: flex; justify-content: space-between">
                                                    <h5 class="mb-0">
                                                        <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?= lang('messages_lang.labelle_historique') ?>
                                                    </button>
                                                </h5>
                                            </div>
                                        </div>
                                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                            <?php include  'includes/Detail_View.php'; 
                                            ?>
                                        </div>
                                    </div>

                                    <form action="<?= base_url('double_commande_new/Phase_comptable/save_prise_en_charge_comptable') ?>" id="MyFormData" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id['EXECUTION_BUDGETAIRE_ID'] ?>">
                                        <input type="hidden" name="ETAPE_ID" class="form-control" value="<?= $id['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                                        <input type="hidden" name="fourn" id="fourn" value="<?=$infosup['TYPE_BENEFICIAIRE_ID'] ?>">
                                        <input name="bene" type="hidden" value="<?= $infosup['DESC_TYPE_BENEFICIAIRE'] ?>">
                                        <input type="hidden" name="id_detail" value="<?= $id['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                                        <input type="hidden" id="type_bene" name="type_bene" value="<?= $infosup['TYPE_BENEFICIAIRE_ID'] ?>">
                                        <input type="hidden" id="ordonancement_id" name="ordonancement" value="<?= $etapes['MONTANT_ORDONNANCEMENT'] ?>">
                                        <input type="hidden" id="DATE_TRANSMISSION" name="date_insertion_check" value="<?=$historique_data_insertion['DATE_TRANSMISSION'] ?>">
                                        <input class="form-control" type="hidden" id="type_montant " name="type_montants" value="<?= $infosup['DEVISE_TYPE_ID'] ?>">
                                        <input type="hidden" id="MONTANT_DEVISE_ORDONNANCEMENT" name="MONTANT_DEVISE_ORDONNANCEMENT" value="<?=$infosup['MONTANT_ORDONNANCEMENT'] ?>">

                                        <div class="col-md-12 container" style="border-radius:10px">
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label for=""><?= lang('messages_lang.date_reception_phase_comptable_prise_en_charge') ?><font color="red">* 
                                                        </font>
                                                    </label>
                                                    <input type="date" min="<?=date('Y-m-d', strtotime($historique_data_insertion['DATE_TRANSMISSION']))?>" name="date_reception" max="<?= date('Y-m-d') ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" id="date_reception_id" class="form-control">
                                                    <font color="red" id="date_reception_error"></font>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="">
                                                    <label for=""><?= lang('messages_lang.label_date_transmission_') ?><font color="red">
                                                    *</font>
                                                </label>
                                                <input type="date" name="date_transmission" onkeypress="return false" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_transmission_id" class="form-control">
                                                <font color="red" id="date_transmission_error">
                                                </font>
                                            </div>
                                        </div>
                                        <?php if ($infosup['TYPE_BENEFICIAIRE_ID'] != 2) : ?>
                                           <div class="col-md-4">
                                            <div class="">
                                                <label for=""><?= lang('messages_lang.date_envoie_obr_phase_comptable_prise_en_charge') ?><font color="red">
                                                *</font>
                                            </label>
                                            <input type="date" name="date_envoie_obr" onkeypress="return false" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_envoie_obr_id" class="form-control">
                                            <font color="red" id="date_envoie_obr_error">
                                            </font>
                                        </div>
                                    </div>
                                <?php endif ?>
                                <div class="col-md-4">
                                    <div>
                                        <label for=""> <?= lang('messages_lang.date_prise_en_charge_phase_comptable_prise_en_charge_comptable') ?> <font color="red">*</font>
                                        </label>
                                        <input type="date" name="date_prise_en_charge" onkeypress="return false" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_prise_en_charge_id" class="form-control">
                                        <font color="red" id="date_prise_en_charge_error"></font>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <br>
                                    <div class="">
                                        <label for=""><?= lang('messages_lang.banque_phase_comptable_prise_en_charge') ?> <font color="red">*</font>
                                        </label>

                                        <select name="Banquess" id="Banque_id" class="form-control select2 " onchange='getAutreBanque(this.value)'>
                                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                                            <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                            <?php foreach ($get_banque as $banque) : ?>
                                                <option value="<?= $banque->BANQUE_ID ?>">
                                                    <?= $banque->NOM_BANQUE ?>
                                            <?php endforeach ?>
                                            </option>
                                        </select>
                                        <font color="red" id="banque_error"></font>
                                    </div>
                                </div>

                                <div id="autre_banq" class="row col-md-12" style="display: none">
                                <div id="autre_banq" class="col-md-4">
                                    <div class="">
                                        <label for=""><?=lang('messages_lang.autr_banq')?><font color="red">*</font>
                                        </label>
                                        <input type="text" class="form-control" id="DESCRIPTION_BANQUE" placeholder="<?=lang('messages_lang.autr_banq')?>" name="DESCRIPTION_BANQUE">
                                    </div>
                                     <font color="red" id="error_desc_banque"></font>
                                </div>
                                <div class="col-md-4">
                                  <div class="">
                                      <label for=""><?=lang('messages_lang.labelle_adresse')?></label>
                                      <input type="text" class="form-control" id="ADRESSE_BANQUE" placeholder="<?=lang('messages_lang.labelle_adresse')?>" name="ADRESSE_BANQUE">
                                  </div>  
                                </div>
                                <div class="col-md-4">
                                  <div class="">
                                      <label for=""><?= lang('messages_lang.type_inst_fin') ?> <font color="red">*</font>
                                      </label>

                                      <select name="TYPE_INSTITUTION_FIN_ID" id="TYPE_INSTITUTION_FIN_ID" class="form-control">
                                          <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                                          <?php foreach ($get_inst_fin as $type_fin) : ?>
                                              <option value="<?= $type_fin->TYPE_INSTITUTION_FIN_ID  ?>">
                                                  <?= $type_fin->DESC_TYPE ?>
                                              <?php endforeach ?>
                                          </option>
                                      </select>
                                      <font color="red" id="type_fin_error"></font>
                                  </div>
                                  <br>
                                    <div class="row">
                                        <br>
                                        <div class="col-md-12 text-right">
                                            <button  id="save_bank_id" type="button" class="btn btn-success" onclick="save_newBanque()"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                              </div>

                                <div class="col-md-4">
                                    <div class="">
                                        <br>
                                        <label for=""><?= lang('messages_lang.numero_du_compte_banquer_phase_comptable_prise_en_charge') ?><font color="red">*</font>
                                        </label>
                                        <input type="" name="num_compte" id="num_compte_id" class="form-control">
                                        <font color="red" id="numer_compte_error"></font>
                                        <font color="green" id="charCount1"></font>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="">
                                        <br>
                                        <label for=""><?= lang('messages_lang.analyse_phase_comptable_prise_en_charge') ?><font color="red">*</font>
                                        </label>
                                        <select style="background-color: rgb(226, 226, 226) !important;" name="analyse[]" id="analyse_id" class="form-control select2" multiple>
                                            <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                                            <?php foreach ($motif as $analyse) : ?>
                                                <option value="<?= $analyse->BUDGETAIRE_TYPE_ANALYSE_ID ?>">
                                                    <?= $analyse->DESC_BUDGETAIRE_TYPE_ANALYSE ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                        <font color="red" id="analyse_error"></font>

                                    </div>
                                </div>



                                <input type="hidden" value="<?= $devise_type ?>" name="devise_type">

                                <?php if ($devise_type == 1) { ?>

                                    <div class="col-md-4">
                                        <div class="">
                                            <br>
                                            <label for=""> <?= lang('messages_lang.decision_phase_comptable_prise_en_charge') ?> <font color="red">*</font> </label>
                                            <select name="OPERATION" id="OPERATION" class="form-control" onchange="get_rejet()">
                                                <option value=""> -- Sélectionner -- </option>
                                                <?php foreach ($confirmation_formulaire_data as $confirmation_formulaire) : ?>
                                                    <option value="<?= $confirmation_formulaire->ID_OPERATION ?>"> <?= $confirmation_formulaire->DESCRIPTION ?> </option>
                                                <?php endforeach ?>
                                            </select>
                                            <font color="red" id="OPERATION_ERROR"></font>
                                        </div>
                                    </div>

                                <?php  } else { ?>
                                    <div class="col-md-4">
                                        <div class="">
                                            <br>
                                            <label for="">
                                                <?= lang('messages_lang.montant_paiement_devise_phase_comptable_prise_en_charge') ?>
                                                <font color="red"></font>
                                            </label>
                                            <input onkeyDown="get_montant2()" type="text" name="paiement_montant_devise" class="form-control" id="paiement_montant_devise_id" value="<?=  number_format($id['MONTANT_LIQUIDATION_DEVISE'], 4, ',', ' ' ) ?>" disabled>
                                            <font color="red" id="paiement_montant_devise_error">
                                            </font>
                                        </div>
                                        <input type="hidden" value="<?= $id['MONTANT_LIQUIDATION_DEVISE'] ?>" name="paiement_montant_dev">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="">
                                            <br>
                                            <label for="">
                                                <?= lang('messages_lang.date_paiement_devise_phase_comptable_prise_en_charge') ?>
                                                <font color="red">*</font>
                                            </label>
                                            <input type="date" name="date_paiement_devise" id="date_paiement_devise_id" class="form-control" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                                            <font color="red" id="date_paiement_devise_error">
                                            </font>
                                        </div>
                                    </div>

                                    <!-- <input type="hidden" name="cour_paiement_devise" id="cour_paiement_devise_id" value="<?//= $taux_echange_taux_request ?>"> -->

                                    <div class="col-md-4">
                                        <div class="">
                                            <br>
                                            <label for="">
                                                <?= lang('messages_lang.decision_phase_comptable_prise_en_charge') ?>
                                                <font color="red">*</font>
                                            </label>

                                            <select name="OPERATION" id="OPERATION" class="form-control" onchange="get_rejet()">
                                                <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                                                <?php foreach ($confirmation_formulaire_data as $confirmation_formulaire) : ?>
                                                    <option value="<?= $confirmation_formulaire->ID_OPERATION ?>"> <?= $confirmation_formulaire->DESCRIPTION ?> </option>
                                                <?php endforeach ?>
                                            </select>
                                            <font color="red" id="OPERATION_ERROR"></font>
                                        </div>
                                    </div>
                                <?php  }  ?>

                                <div class="col-md-4" id="amount_payment">
                                    <div class="">
                                        <br>
                                        <label for=""> <?= lang('messages_lang.montant_paiement_phase_comptable_prise_en_charge') ?> <font color="red">*</font></label>
                                        <input type="text" value="<?=  number_format($id['MONTANT_LIQUIDATION'], 4, ',', ' ' ) ?>" class="form-control" id="paiement_id" disabled>
                                        <font color="red" id="montant_paiement_error">
                                        </font>
                                    </div>
                                    <input type="hidden" value="<?= $id['MONTANT_LIQUIDATION'] ?>" name="paiement_montant">
                                </div>

                                <input type="hidden" name="id_crypt" value="<?= $id_crypt ?>">
                                <div class="col-md-4" id="show_motif" style="display:none;">
                                    <div class="">
                                        <br>
                                        <label for=""><?=lang('messages_lang.motif_retour_correction_phase_comptable_prise_en_charge')?><font color="red">*</font><span id="loading_motif"></span></label>
                                        <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
                                           <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                           <?php
                                           foreach($motif_2 as $value)
                                           { 
                                              if($value->TYPE_ANALYSE_MOTIF_ID==set_value('TYPE_ANALYSE_MOTIF_ID')){?>
                                                <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>" selected><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                            <?php }else                                
                                            {
                                                ?>
                                                <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>"><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <br>
                                    <span id="autre_motif" class="col-md-12 row" style="display: none">
                                        <div class="col-md-9">
                                          <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_MOTIF">
                                      </div>
                                      <div class="col-md-2" style="margin-left: 5px;">
                                          <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                                      </div>
                                  </span>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                                <?php endif ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="">
                                <br>
                                <label for="">
                                    <?= lang('messages_lang.table_date_paie') ?>
                                    <font color="red">*</font>
                                </label>
                                <input type="date" name="date_paiement_devise" id="date_paiement_devise_id" class="form-control" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                                <font color="red" id="date_paiement_devise_error">
                                </font>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="">
                                <br>
                                <label for="">
                                    <?= lang('messages_lang.motif_paiement_phase_comptable_prise_en_charge') ?>
                                    <font color="red">*</font>
                                </label>
                                <textarea name="motif_paie" onKeyDown="check_caractere()" id="motif_paie_id" class="form-control"><?=$id['COMMENTAIRE']?></textarea>
                                <font color="red" id="motif_paie_error"></font><br>
                                <font color="green" id="charCount"></font>
                            </div>
                        </div>

                    </div>
                </div>

                <div style="float:right">
                    <a id="valid_btn" class="btn btn-primary" onclick="save_dossier();" class="form-control"><?= lang('messages_lang.enregistre_phase_comptable_prise_en_charge') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
</div>

<div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="height: calc(100vh - 50px); overflow: auto">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_phase_comptable_prise_en_charge') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive overflow-auto mt-2">
                    <table class=" table  m-b-0 m-t-20">
                        <tbody>
                            <tr>
                                <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_reception_phase_comptable_prise_en_charge') ?></td>
                                <td id="date_reception_id_modal"></td>
                            </tr>
                        </tr>

                        <tr>
                            <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_transmission_phase_comptable_prise_en_charge') ?></td>
                            <td id="date_transmission_id_modal"></td>
                        </tr>
                    </tr>

                    <tr>
                        <td> <i class="fa fa-house"></i> <?= lang('messages_lang.banque_phase_comptable_prise_en_charge') ?></td>
                        <td id="banque_id_modal"></td>
                    </tr>
                </tr>

                <tr>
                    <td> <i class="fa fa-cogs"></i> <?= lang('messages_lang.numero_du_compte_banquer_phase_comptable_prise_en_charge') ?></td>
                    <td id="compte_id_modal"></td>
                </tr>
            </tr>

            <tr id="motif_zone">
                <td> <i class="fa fa-sms"></i> <?= lang('messages_lang.motif_paiement_phase_comptable_prise_en_charge') ?></td>
                <td id="motif_id_modal"></td>
            </tr>
        </tr>

        <tr>
            <td> <i class="fa fa-cogs"></i> <?= lang('messages_lang.analyse_phase_comptable_prise_en_charge') ?></td>
            <td id="analyse_id_modal"></td>
        </tr>

        <tr>
            <td> <i class="fa fa-credit-card"></i> <?= lang('messages_lang.montant_paiement_phase_comptable_prise_en_charge') ?></td>
            <td id="montant_en_bif_modal"></td>
        </tr>

        <tr class="d-none">
            <td> <i class="fa fa-mail-reply"></i> <?= lang('messages_lang.motif_retour_correction_phase_comptable_prise_en_charge') ?></td>
            <td id="analyse_modal"></td>
        </tr>

                                    <!-- <tr class="d-none">
                                        <td> <i class="fa fa-clone"></i> <?//= lang('messages_lang.etap_corrige_phase_comptable_prise_en_charge') ?></td>
                                        <td id="etap_a_corrige_modal"></td>
                                    </tr> -->

                                    <?php if ($infosup['TYPE_BENEFICIAIRE_ID'] != 2) : ?>
                                        <tr>
                                            <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_envoie_obr_phase_comptable_prise_en_charge') ?></td>
                                            <td id="date_id_modal"></td>
                                        </tr>
                                    </tr>
                                <?php endif ?>

                                <?php if ($infosup['DEVISE_TYPE_ID'] != 1) { ?>

                                    <tr>
                                        <td> <i class="fa fa-credit-card"></i> <?= lang('messages_lang.montant_paiement_devise_phase_comptable_prise_en_charge') ?></td>
                                        <td id="paiement_montant_devise_id_modal"></td>
                                    </tr>
                                </tr>

                                <tr>
                                    <td> <i class="fa fa-sms"></i> <?= lang('messages_lang.cour_paiement_devise_phase_comptable_prise_en_charge') ?></td>
                                    <td id="cour_paiement_devise_id_modal"></td>
                                </tr>
                            </tr>

                            <tr>
                                <td> <i class="fa fa-sms"></i> <?= lang('messages_lang.date_paiement_devise_phase_comptable_prise_en_charge') ?></td>
                                <td id="date_paiement_devise_id_modal"></td>
                            </tr>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <button id="mod" type="button" class="btn btn-secondary" style="border-radius: .2rem;" data-dismiss="modal"> <i class=" fa fa-edit"></i>
            <?= lang('messages_lang.modifier_phase_comptable_prise_en_charge') ?></button>
            <button id="myElement" onclick="save_info();hideButton()" type="button" class="btn btn-info"><i class="fa fa-check"></i>
                <?= lang('messages_lang.confirmer_phase_comptable_prise_en_charge') ?></button>
            </div>
        </div>
    </div>
</div>
<?php echo view('includesbackend/scripts_js.php'); ?>

</body>

</html>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
}
</script>
<!-- Save dossier -->
<script>

    function save_dossier() {

        var statut = true;
        var date_reception_id = $('#date_reception_id').val();
        var date_transmission_id = $('#date_transmission_id').val();
        var date_obr_id = $('#date_envoie_obr_id').val();
        var motif = $('#motif_paie_id').val();
        var num_compte_id = $('#num_compte_id').val();
        var analyses = $('#analyse_id').val();
        var docu_upload = $('#upload_doc_id').val();
        var Banques_id = $('#Banque_id').val();
        var cour_paiement_devise_id = $('#cour_paiement_devise_id').val();
        var date_paiement_devise_id = $('#date_paiement_devise_id').val();
        var paiement_montant_devise_id = $("#paiement_montant_devise_id").val();
        var date_envoie_obr_id = $("#date_envoie_obr_id").val();
        var type_montant = $('#type_montant').val();
        var paiement_id = $('#paiement_id').val();

        var OPERATION_ID = $('#OPERATION').val();

        if (OPERATION_ID == "") {
            statut = false;
            $("#OPERATION_ERROR").html('<?= lang("messages_lang.champ_obligatoire_phase_comptable_prise_en_charge") ?>');
        } else {
            $("#OPERATION_ERROR").html("");
        }

        if (paiement_id == "") {
            statut = false;
            $("#montant_paiement_error").html('<?= lang("messages_lang.champ_obligatoire_phase_comptable_prise_en_charge") ?>');
        } else {
            $("#montant_paiement_error").html("");
        }


        if (type_montant == 1) {
            if (paiement_id == "") {
                statut = false;
                $("#montant_paiement_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
            } else {
                $("#montant_paiement_error").html("");
            }

            if (parseInt(paiement) > parseInt(ordonancement_id)) {
                statut = false;
                $('#montant_paiement_error').html("<?= lang("messages_lang.montant_superieur_ordonancement_un") ?>");
            } else {
                $('#montant_paiement_error').html('');

            }

            if (date_envoie_obr_id == "") {
                statut = false;
                $("#date_envoie_obr_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
            } else {
                $("#date_envoie_obr_error").html("");
            }

        }

        if (date_paiement_devise_id == "") {
            statut = false;
            $("#date_paiement_devise_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#date_paiement_devise_error").html("");
        }

        if (cour_paiement_devise_id == "") {
            statut = false;
            $("#cour_paiement_devise_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#cour_paiement_devise_error").html("");
        }


        if (docu_upload == "") {
            statut = false;
            $("#upload_doc_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#upload_doc_error").html("");
        }
        if (analyses == "") {
            statut = false;
            $("#analyse_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#analyse_error").html("");
        }

        if (num_compte_id == "") {
            statut = false;
            $("#numer_compte_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#numer_compte_error").html("");
        }

        if (motif == "") {
            statut = false;
            $("#motif_paie_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#motif_paie_error").html("");
        }

        if (Banques_id == "") {
            statut = false;
            $("#banque_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#banque_error").html("");
        }

        if (date_reception_id == "") {
            statut = false;
            $("#date_reception_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {

            $("#date_reception_error").html("");
        }

        if (date_transmission_id == "") {
            statut = false;
            $("#date_transmission_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
        } else {
            $("#date_transmission_error").html("");
        }


        if (statut == true) {

            var date = moment(date_reception_id, "YYYY/mm/DD")
            var reception_date = date.format('DD/mm/YYYY')
            var date1 = moment(date_transmission_id, "YYYY/mm/DD")
            var transmission_date = date1.format('DD/mm/YYYY')
            var date3 = moment(date_obr_id, "YYYY/mm/DD")
            var obr_date = date3.format('DD/mm/YYYY')
            var date4 = moment(date_paiement_devise_id, "YYYY/mm/DD")
            var paie_devise = date4.format('DD/mm/YYYY')
            $('#date_reception_id_modal').html(reception_date);
            $('#date_transmission_id_modal').html(transmission_date);
            $('#banque_id_modal').html($('#Banque_id option:selected').text());
            $('#compte_id_modal').html(num_compte_id);
            $('#motif_id_modal').html(motif);
            $('#date_id_modal').html(obr_date);
            $('#paiement_montant_devise_id_modal').html(paiement_montant_devise_id);
            $('#date_paiement_devise_id_modal').html(paie_devise);
            $('#cour_paiement_devise_id_modal').html(cour_paiement_devise_id); 
            var TYPE_ANALYSE = $('#analyse_id option:selected').toArray().map(item => '<li>' + item.text + '</li>').join('')
            $('#analyse_id_modal').html('<ul>' + TYPE_ANALYSE + '</ul>');

            let OPERATION_VERIFIER = $("#OPERATION").val()
            $("#montant_en_bif_modal").html($("#paiement_id").val())
            if (OPERATION_VERIFIER == 1) {
                let ALL_ANALYSE = $("#TYPE_ANALYSE_MOTIF_ID option:selected").toArray().map(items => '<li>' + items.text + '</li>').join('')
                document.querySelector("#analyse_modal").parentElement.classList.remove('d-none')
                // document.querySelector("#etap_a_corrige_modal").parentElement.classList.remove('d-none')
                $("#analyse_modal").html('<ul>' + ALL_ANALYSE + '</ul>')
                // $("#etap_a_corrige_modal").html($('#ETAP_PRISE_EN_CHARGE option:selected').text())
            }

            $('#detail').modal('show')

        }
    }
</script>

<script type="text/javascript">
    function DoPrevent(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    $('#paiement_id').on('input', function() {
        var value = $(this).val();
        value = value.replace(/[^0-9.]/g, '');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        $(this).val(value);
        if (/^0\d/.test(value)) {
            value = value.replace(/^0\d/, '');
            $(this).val(value);
        }

        var ordonancement_id = $('#ordonancement_id').val();
        var paiement = $('#paiement_id').val().replace(/ /g, '');


        if (parseInt(paiement) > parseInt(ordonancement_id)) {
            $('#paiement_id').on('keypress', DoPrevent);
            $('#montant_paiement_error').html("<?= lang("messages_lang.montant_superieur_ordonancement") ?>");
            $('#valid_btn').hide();
        } else {
            $('#montant_paiement_error').html('');
            $('#paiement_id').off('keypress', DoPrevent);
            $('#valid_btn').show();
        }
    })

    function get_min_trans() {
        $("#date_transmission_id").prop('min', $("#date_reception_id").val());
    }

    function get_rejet() {
        var OPERATION = $('#OPERATION').val();
        if (OPERATION == '') {
            $('#show_motif').hide();
            $('#show_etape').hide();
            $('#amount_payment').show();
        } else {
            if (OPERATION == 1 || OPERATION == 3) {
                $('#show_motif').show();
                $('#show_etape').show();
                $('#amount_payment').hide();
            } else {
                $('#show_motif').hide();
                $('#show_etape').hide(); 
                $('#amount_payment').show();               
            }
        }

        $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
        $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
        $('#autre_motif').delay(100).hide('show');
        
    }

    $(document).ready(function() {
        var type_bene = $('#type_bene').val();
        if (type_bene == 2) {
            $("#obr_date").show();
        } else {
            $("#obr_date").hide();
        }
    });

    $('#message').delay('slow').fadeOut(3000);

    function verif_montant() {
        var liquidation = $('#liquidation_id').val();
        var paiement = $('#montant_paiment_id').val();

        if (parseInt(paiement) > parseInt(liquidation)) {
            $('#paiment_error').attr('disabled', true);

        }
    }

    function get_montant2() {
        var MONTANT_DEVISE_ORDONNANCEMENT = $('#MONTANT_DEVISE_ORDONNANCEMENT').val();
        var paiement_montant_devise_id = $('#paiement_montant_devise_id').val();
        if (parseInt(paiement_montant_devise_id) > parseInt(MONTANT_DEVISE_ORDONNANCEMENT)) {
            $('#paiement_montant_devise_id').on('keypress', DoPrevent);
            $('#paiement_montant_devise_error').html("<?= lang("messages_lang.montant_superieur_ordonancement_devise") ?>");

        } else {
            $('#paiement_montant_devise_error').html('');
            $('#paiement_montant_devise_id').off('keypress', DoPrevent);
        }
    }

    function check_caractere1() {
        var input = document.getElementById("num_compte_id");
        var charCount = document.getElementById("charCount1");

        input.addEventListener("input", function() {
            var text = input.value;
            var count = text.length;
            charCount.textContent = "<?= lang('messages_lang.Nombre_caracteres') ?> : " + count;

            var maxLength = 20;
            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
        });

        $('#num_compte_id').on('input', function() {
            if (this.id === "num_compte_id") {
                $(this).val($(this).val().toUpperCase());
                $(this).val(this.value.substring(0, 20));
            }
        })
    }

    function check_caractere() {
        var input = document.getElementById("motif_paie_id");
        var charCount = document.getElementById("charCount");

        input.addEventListener("input", function() {
            var text = input.value;
            var count = text.length;
            charCount.textContent = "<?= lang('messages_lang.Nombre_caracteres') ?> : " + count;

            var maxLength = 100;
            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
        });
    }

    function save_info() {
        $('#MyFormData').submit()
    }
</script>

<script type="text/javascript">
function getAutreMotif(id = 0)
{
    var selectElement = document.getElementById("TYPE_ANALYSE_MOTIF_ID");
    if (id.includes("-1")) {
      $('#autre_motif').delay(100).show('hide');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      disableOptions(selectElement);

  }else{
      $('#autre_motif').delay(100).hide('show');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      enableOptions(selectElement);
  }

}

function disableOptions(selectElement) {
  for (var i = 0; i < selectElement.options.length; i++) {
      if (selectElement.options[i].value !== "-1") {
        selectElement.options[i].disabled = true;
    }
  }
}

function enableOptions(selectElement) {
    for (var i = 0; i < selectElement.options.length; i++) {
      selectElement.options[i].disabled = false;
  }
}

function save_newMotif()
{
  var DESCRIPTION_MOTIF = $('#DESCRIPTION_MOTIF').val();
  var statut = 2;
  
  if (DESCRIPTION_MOTIF == "") {
    
      $('#DESCRIPTION_MOTIF').css('border-color','red');
      statut = 1;
  }

  if(statut == 2)
  {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Phase_comptable/save_newMotif",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_MOTIF:DESCRIPTION_MOTIF
      },
      beforeSend: function() {
          $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data) {
          $('#TYPE_ANALYSE_MOTIF_ID').html(data.motifs);
          TYPE_ANALYSE_MOTIF_ID.InnerHtml=data.motifs;
          $('#loading_motif').html("");
          $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          $('#autre_motif').delay(100).hide('show');
      }
  });
  }
}
</script>

<script type="text/javascript">
    function getAutreBanque(id = 0) {
      var selectElement = document.getElementById("Banque_id");
      if (id.includes("-1")) {
        $('#autre_banq').delay(100).show('hide');
        $('#DESCRIPTION_BANQUE').val('');
        $('#DESCRIPTION_BANQUE').attr('placeholder', '<?=lang('messages_lang.autr_banq')?>');
        $('#ADRESSE_BANQUE').val('');
        $('#ADRESSE_BANQUE').attr('placeholder', '<?=lang('messages_lang.labelle_adresse')?>');
        $('#TYPE_INSTITUTION_FIN_ID').val('');
        
      } else {
        $('#autre_banq').delay(100).hide('show');
        $('#DESCRIPTION_BANQUE').val('');
        $('#DESCRIPTION_BANQUE').attr('placeholder', '<?=lang('messages_lang.autr_banq')?>');
        $('#ADRESSE_BANQUE').val('');
        $('#ADRESSE_BANQUE').attr('placeholder', '<?=lang('messages_lang.labelle_adresse')?>');
        $('#TYPE_INSTITUTION_FIN_ID').val('');
        
      }
    }


    function save_newBanque() {
      var DESCRIPTION_BANQUE = $('#DESCRIPTION_BANQUE').val();
      var ADRESSE_BANQUE = $('#ADRESSE_BANQUE').val();
      var TYPE_INSTITUTION_FIN_ID = $('#TYPE_INSTITUTION_FIN_ID').val();
      var statut = 2;
      $('#error_desc_banque, #type_fin_error').html('');
      if (DESCRIPTION_BANQUE === "") {
        statut = 1;
        $('#error_desc_banque').html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
      }

      if(!TYPE_INSTITUTION_FIN_ID) {

        statut = 1;
        $('#type_fin_error').html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
      }

      

      if (statut == 2) {
        $('#save_bank_id').prop('disabled',true);
        $.ajax({
          url: "<?=base_url('')?>/double_commande_new/Phase_comptable/save_newBanque",
          type: "POST",
          dataType: "JSON",
          data: {
            DESCRIPTION_BANQUE: DESCRIPTION_BANQUE,
            ADRESSE_BANQUE: ADRESSE_BANQUE,
            TYPE_INSTITUTION_FIN_ID: TYPE_INSTITUTION_FIN_ID
          },
          beforeSend: function() {
            $('#loading_banque').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          },
          success: function(data) {

            $('#Banque_id').html(data.banks);
            Banque_id.innerHTML = data.banks;
            $('#loading_banque').html("");
            $('#Banque_id').val([]).trigger('change');
            $('#DESCRIPTION_BANQUE').attr('placeholder', '<?=lang('messages_lang.autr_banq')?>');
            $('#autre_banque').delay(100).hide('show');
            $('#ADRESSE_BANQUE').attr('placeholder', '<?=lang('messages_lang.labelle_adresse')?>');
            $('#TYPE_INSTITUTION_FIN_ID').val('');
            $('#error_desc_banque').html('');
            $('#save_bank_id').prop('disabled',false)
           
          }
        });
      }
    }
</script>