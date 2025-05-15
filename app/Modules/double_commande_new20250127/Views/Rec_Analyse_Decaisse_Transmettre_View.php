<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <?php $validation = \Config\Services::validation(); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
          </div>
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-10">
                      <h3 class="header-title text-black"><?= lang('messages_lang.title_global')?>:<?php echo $etape['DESC_ETAPE_DOUBLE_COMMANDE']?></h3>
                    </div>

                    <div class="col-md-2" style="float: right;">
                      <a href="<?=base_url('double_commande_new/Liste_Decaissement')?>" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-list text-light" aria-hidden="true"></i><?= lang('messages_lang.link_list')?></a>
                    </div>
                  </div>
                </div><hr>
                <?php
                    if(session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="col-md-12">
                        <div class="w-100 bg-danger text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                      </div>
                      <?php
                    }
                    ?>
                <!-- debut -->
                <div class="row col-md-12" style="width:90%">
                  <div id="accordion">
                    <div class="card-header" id="headingThree">
                      <h5 class="mb-0">
                        <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;margin-left:0px;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?= lang('messages_lang.labelle_historique')?>
                        </button>
                      </h5>
                    </div>  
                  </div>
                  <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <?php include  'includes/Detail_View.php'; ?>
                  </div>
                </div>
                <!--fin-->
                <div class="car-body">
                  <form id='MyFormData' enctype='multipart/form-data' action="<?=base_url('double_commande_new/Phase_comptable/Rec_Analyse_Decaisse_Transmettre/save') ?>" method="POST">
                    <input type="hidden" name="id_raccrochage" id="id_raccrochage" value="<?=$etape['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                    <input type="hidden" name="etape" id="etape" value="<?=$etape['ETAPE_DOUBLE_COMMANDE_ID']?>">
                    <input type="hidden" name="TAUX_ECHANGE_ID"  value="<?= $detai_taux_echange_id ?>">
                    <input type="hidden" name="MONTANT_DEVISE_PAIEMENT" value="<?=$etape['ENG_BUDGETAIRE_DEVISE']?>" id="MONTANT_DEVISE_PAIEMENT">

                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6"><br>
                          <label><?= lang('messages_lang.label_decision')?><span style="color: red;">*</span></label>
                          <select  class="form-control" name="ID_OPERATION" id="ID_OPERATION" onchange="get_rejet(this)">
                            <option value=""><?= lang('messages_lang.label_selecte')?></option> 
                              <?php foreach($operation as $key){
                                if($key->ID_OPERATION ==set_value('ID_OPERATION')){
                                  echo "<option value='".$key->ID_OPERATION."' >".$key->DESCRIPTION."</option>";
                                }
                                else{
                                  echo "<option value='".$key->ID_OPERATION."' >".$key->DESCRIPTION."</option>";
                                }
                              }
                            ?>
                          </select>
                          <span id="error_operation" class="text-danger"></span>
                        </div>
                        
                        <?php if ($detai_taux_echange_id == 1) { ?>
                          <div class="col-md-6">
                            <br>
                              <label><?= lang('messages_lang.label_montant_dec')?> <span style="color: red;">*</span></label>
                              <input type="text" class="form-control" value="<?= number_format($bif_decais,2,","," ")?>" id="MONTANT_DECAISSE123" readonly>
                              <input type="hidden" name="MONTANT_DECAISSE" id="MONTANT_DECAISSE" value="<?=$bif_decais?>">
                              <input type="hidden" name="bif_dec" id="bif_dec" value="<?=$bif_decais?>">
                              <span id="error_DECAISSE" class="text-danger"></span>
                          </div>

                        <?php } else { ?>
                          <div class="col-md-6"> <br>
                            <label><?= lang('messages_lang.label_montant_devise_dec')?> <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" value="<?= number_format($devise_decais,2,","," ")?>" id="MONTANT_DECAISSE_ID123" readonly>
                             <input type="hidden" name="MONTANT_DECAISSE_ID" id="MONTANT_DECAISSE_ID" value="<?=$devise_decais?>">
                            <input type="hidden" id="devise_dec" name="devise_dec" value="<?=$devise_decais?>">
                            <span id="error_DECAISSE_ID" class="text-danger"></span>
                          </div>

                          <div class="col-md-6">
                            <br>
                              <label> <?= lang('messages_lang.table_date_devise')?></label>
                              <input type="date" class="form-control" max="<?= date('Y-m-d') ?>" name="DATE_COUR_DEVISE" id="DATE_COUR_DEVISE">
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_COUR_DEVISE'); ?>
                              <?php endif ?>
                              <span id="error_date_devise_dec" class="text-danger"></span>
                          </div>
                          <div class="col-md-6" id="dec" style="display:none;"> <br>
                            <label for=""><?= lang('messages_lang.label_montant_BIF_dec')?> <font color="red">*</font></label>
                            <input readonly type="" name="total" id="total_devise" class="form-control">
                          </div>
                        <?php } ?>

                        
                        <div class="col-md-6"><br>
                          <label><?= lang('messages_lang.label_date_dec')?><span style="color: red;">*</span></label>
                          <input type="date" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" class="form-control" name="DATE_DECAISSEMENT" id="DATE_DECAISSEMENT" value="<?=set_value('DATE_DECAISSEMENT')?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DATE_DECAISSEMENT'); ?>
                          <?php endif ?>
                          <span id="error_dat_dec" class="text-danger"></span>
                        </div>
                                                  
                        <div class="col-md-6" id="show_motif" style="display:none;"><br>
                          <label for=""><?= lang('messages_lang.label_motif_dec')?><font color="red">*</font></label>
                          <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple>
                            <?php
                            foreach($motif as $value)
                            { 
                              if($value->TYPE_ANALYSE_MOTIF_ID==set_value('TYPE_ANALYSE_MOTIF_ID')){?>
                                <option value="<?=$value->TYPE_ANALYSE_ID ?>" selected><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                              <?php }else                                
                              {
                                ?>
                                <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>"><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                          <span id="error_motif" class="text-danger"></span>  
                        </div>
                          
                        <div class="col-md-6"> <br>
                          <label><?= lang('messages_lang.label_transmission_date_dec')?><span style="color: red;">*</span></label>
                          <input type="date" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" class="form-control" name="DATE_TRANSMISSION" value="<?=set_value('DATE_TRANSMISSION')?>" max="<?=date('Y-m-d')?>" id="DATE_TRANSMISSION" onkeypress="return false" onblur="this.type='date'">
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DATE_TRANSMISSION'); ?>
                          <?php endif ?>
                          <span id="error_dat_trans" class="text-danger"></span>
                        </div>
                      </div>
                    </form>
                    <div class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_enregistrer')?></a>
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
  <!--******* Modal pour confirmer les infos saisies *********-->
  <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_confirmation')?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="table-responsive  mt-3">
            <table class="table m-b-0 m-t-20">
              <tbody>
                <tr>
                  <td><i class="fa fa-list"></i> &nbsp;<strong><?= lang('messages_lang.label_decision')?></strong></td>
                  <td id="operation_verifie" class="text-dark"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_dec')?></strong></td>
                  <td id="date_dec_verifie" class="text-dark"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_transmission_date_dec')?></strong></td>
                  <td id="date_trans_verifie" class="text-dark"></td>
                </tr>
                <?php if ($detai_taux_echange_id == 1) : ?>
                  <tr>
                  <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.label_montant_dec')?></td>
                  <td id = 'montant_bif'></td>
                </tr>
                <?php endif ?>

                <?php if ($detai_taux_echange_id != 1) : ?>
                  <tr>
                  <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.label_montant_devise_dec')?></td>
                  <td id = 'montant_devise'></td>
                </tr>
                <tr>
                  <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.table_date_devise')?></td>
                  <td id = 'table_date_devise'></td>
                </tr>
              <?php endif ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
            <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-edit" aria-hidden="true"></i>Modifier</button>
            <a id="myElement" onclick="confirm();hideButton()" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i>Confirmer</a>
        </div> 
      </div>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
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
<script>
    let paiement_montant_devise_id = document.querySelector("#paiement_montant_devise_id");
    let cour_paiement_devise = document.querySelector('[name="cour_paiement_devise"]').value;

    console.log(cour_paiement_devise)
    paiement_montant_devise_id.addEventListener('input', function (e) {
        let paiement_mouvement = e.currentTarget.value
        document.querySelector('#paiement_id').value = (paiement_mouvement * cour_paiement_devise).toFixed(4)
        if ((paiement_mouvement * cour_paiement_devise) == 0) {
            document.querySelector('#paiement_id').value = ''
        }
    })
</script>

<script type="text/javascript">
//function pour donner la date minimum de transmission
  function get_date_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
    $("#DATE_DECAISSEMENT").prop('min',$("#DATE_RECEPTION").val());
  }
</script>
<script type="text/javascript">
  function save()
  {
    var DATE_TRANSMISSION  = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION  = $('#DATE_RECEPTION').val();
    var MONTANT_DECAISSE = $('#MONTANT_DECAISSE').val();
    var DATE_DECAISSEMENT = $('#DATE_DECAISSEMENT').val();
    var ID_OPERATION = $('#ID_OPERATION').val();
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();
    var DATE_DEVISE_DECAISSEMENT = $('#DATE_COUR_DEVISE').val();
    var MONTANT_DECAISSE_DEVISE = $('#MONTANT_DECAISSE_ID').val();
    var TAUX_ECHANGE_ID = $('#TAUX_ECHANGE_ID').val();
    var bif_dec = $('#bif_dec').val();
    var devise_dec = $('#devise_dec').val();

    $('#error_dat_trans').html('');
    $('#error_dat_rec').html('');
    $('#error_dat_dec').html(''); 
    $('#error_operation').html('');
    $('#error_DECAISSE').html(''); 
    $('#error_DECAISSE_ID').html('');   
    $('#error_motif').html('');
    $('#error_date_devise_dec').html('');

    var statut = 2;
    if (TAUX_ECHANGE_ID!=1) {
      if(DATE_DEVISE_DECAISSEMENT  == '')
      {
        $('#error_date_devise_dec').html('<?= lang('messages_lang.validation_message')?>');
        statut = 1;
      }
    }
    if(DATE_TRANSMISSION  == '')
    {
      $('#error_dat_trans').html('<?= lang('messages_lang.validation_message')?>');
      statut = 1;
    }
    if(DATE_RECEPTION  == '')
    {
      $('#error_dat_rec').html('<?= lang('messages_lang.validation_message')?>');
      statut = 1;
    }
    if(DATE_DECAISSEMENT  == '')
    {
      $('#error_dat_dec').html('<?= lang('messages_lang.validation_message')?>');
      statut = 1;
    } 
    if(ID_OPERATION  == '')
    {
      $('#error_operation').html('<?= lang('messages_lang.validation_message')?>');
      statut = 1;
    }

    if(parseFloat(MONTANT_DECAISSE) > parseFloat(bif_dec))
    {
      $('#error_DECAISSE').html('<?= lang('messages_lang.depasse_message')?>  '+bif_dec);
      statut = 1;
    }

    if(MONTANT_DECAISSE <= 0)
    {
      $('#error_DECAISSE').html('<?= lang('messages_lang.negatif_message')?>');
      statut = 1;
    }


    if(parseFloat(MONTANT_DECAISSE_DEVISE) > parseFloat(devise_dec))
    {
      $('#error_DECAISSE_ID').html('<?= lang('messages_lang.depasse_message')?> '+devise_dec);
      statut = 1;
    }

    if(MONTANT_DECAISSE_DEVISE <= 0)
    {
      $('#error_DECAISSE_ID').html('<?= lang('messages_lang.negatif_message')?>');
      statut = 1;
    }
    if (ID_OPERATION==1) {
      if (TYPE_ANALYSE_MOTIF_ID == '') {
      statut = 1;
      $('#error_motif').html('<?= lang('messages_lang.validation_message')?>');
      }
    }
    if(statut == 2)
    {
      $('#myModal').modal('show');
      var date_rec = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var result_rec = date_rec.format("DD/mm/YYYY");
      $('#date_rec_verifie').html(result_rec);

      var date_trans= moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var result_trans = date_trans.format("DD/mm/YYYY");
      $('#date_trans_verifie').html(result_trans);

      var date_dec= moment(DATE_DECAISSEMENT, "YYYY/mm/DD");
      var result_dec = date_dec.format("DD/mm/YYYY");
      $('#date_dec_verifie').html(result_dec);

      var date_devise_dec= moment(DATE_DEVISE_DECAISSEMENT, "YYYY/mm/DD");
      var result_devise_dec = date_devise_dec.format("DD/mm/YYYY");
      $('#table_date_devise').html(result_devise_dec);
      $('#montant_devise').html($('#MONTANT_DECAISSE_ID123').val());
      $('#montant_bif').html($('#MONTANT_DECAISSE123').val());

      var operation= $('#ID_OPERATION option:selected').toArray().map(item =>item.text).join();
      $('#operation_verifie').html(operation);

    }
  }
</script>

<!-- Formatter Cour décaissement en devise* -->
 <script type="text/javascript">
    $('#COUR_DECAISSEMENT_DEVISE').on('input', function() {
      var value = $(this).val();
      value = value.replace(/[^0-9.]/g, '');
      value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
      $(this).val(value);
      if (/^0\d/.test(value)) {
        value = value.replace(/^0\d/, '');
        $(this).val(value);
      }
      get_montant2()
    })
</script> 

<script>
  function confirm(){
     $('#MyFormData').submit();
  }
</script>

<script>
  function DoPrevent(e)
    {
    e.preventDefault();
    e.stopPropagation();
    }

  function get_montant2()
  {
    var MONTANT_DEVISE_PAIEMENT=$('#MONTANT_DEVISE_PAIEMENT').val();
    var MONTANT_DECAISSE_ID =$('#MONTANT_DECAISSE_ID').val();
    var COUR_DECAISSEMENT_DEVISE =$('#COUR_DECAISSEMENT_DEVISE').val();

    if(COUR_DECAISSEMENT_DEVISE != " "){

      var  result= parseInt(MONTANT_DEC) *parseInt(COUR_DEC_DEVISE);
      $('#dec').show();
      $('#total_devise').val(result);
      $('#total_devise').val($('#total_devise').val().replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
    }else{
      $('#dec').hide();
      $('#total_devise').val('');
    }
  }
</script>

<script>
  function get_rejet()
  {
    var OPERATION = $('#ID_OPERATION').val();

    if(OPERATION == '')
    {
      $('#show_motif').hide();
    }else{

      if(OPERATION == 1)
      {
        $('#show_motif').show();
      }else{

        $('#show_motif').hide();
      }

    }

  }
</script>